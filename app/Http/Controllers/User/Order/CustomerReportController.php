<?php

namespace App\Http\Controllers\User\Order;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User\Order\CustomerReport;
use App\Http\Resources\User\CustomerReportResource;

class CustomerReportController extends Controller
{
    // List all reports, with pagination
    public function index(Request $request)
    {
        $query = CustomerReport::with([
            'user',
            'orderItem.order',
            'orderItem.product.seller.user',
        ]);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('issue_type', $type);
        }
        $reports = $query->orderBy('created_at', 'desc')->get();

        return CustomerReportResource::collection($reports);
    }

    // Show single report by id
    public function show($id)
    {
        $report = CustomerReport::with(['user',
            'orderItem.order',
            'orderItem.product.seller.user'])
            ->findOrFail($id);

        return new CustomerReportResource($report);
    }


    // Store new report
    public function store(Request $request)
    {
        $request->validate([
            'order_item_id' => ['required', 'exists:order_items,id'],
            'issue_type' => ['required', Rule::in(['not_received', 'partially_received'])],
            'description' => ['required','min:30', 'string'],
        ]);

        // Prevent duplicate report for same order item by the same user
        $alreadyReported = CustomerReport::where('order_item_id', $request->order_item_id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyReported) {
            return response()->json([
                'message' => 'You have already submitted a report for this order item.'
            ], 409);
        }


        $report = CustomerReport::create([
            'order_item_id'=> $request->order_item_id,
            'issue_type'=> $request->issue_type,
            'description'=>$request->description,
            'status' => 'pending',
            'user_id'=>auth()->id(),
        ]);

        return response()->json([
            "message"=>"Issue Reported successfully"
        ]);
    }


    // Update report status or description
    public function update(Request $request, $id)
    {
        $report = CustomerReport::findOrFail($id);

        $data = $request->validate([
            'issue_type' => [Rule::in(['not_received', 'partially_received'])],
            'description' => ['nullable', 'string'],
            'status' => [Rule::in(['pending', 'in_progress', 'resolved'])],
        ]);

        // Update status and set resolved_at if needed
        if (isset($data['status'])) {
            $report->status = $data['status'];
            $report->resolved_at = $data['status'] === 'resolved' ? now() : null;
        }

        if (isset($data['issue_type'])) {
            $report->issue_type = $data['issue_type'];
        }

        if (array_key_exists('description', $data)) {
            $report->description = $data['description'];
        }

        $report->save();

        return new CustomerReportResource($report);
    }



    public function markInProgress($id)
    {
        $report = CustomerReport::findOrFail($id);

        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Only pending reports can be marked as in progress.'], 400);
        }

        $report->status = 'in_progress';
        $report->save();

        return response()->json(['message' => 'Report marked as in progress.','id'=>$id], 200);
    }


    public function markResolved($id)
    {
        $report = CustomerReport::findOrFail($id);

        if ($report->status !== 'in_progress') {
            return response()->json(['message' => 'Only reports in progress can be resolved.'], 400);
        }

        $report->status = 'resolved';
        $report->resolved_at = now();
        $report->save();

        return response()->json(['message' => 'Report marked as resolved.'], 200);
    }


}
