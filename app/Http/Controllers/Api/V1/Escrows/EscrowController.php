<?php

namespace App\Http\Controllers\Api\V1\Escrows;

use App\Enums\Escrow\PaymentMethod;
use App\Enums\Escrow\Weekday;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Services\Escrows\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EscrowController extends Controller
{
    public function __construct(private EscrowService $service) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'offer_id' => 'required|uuid',
            'buyer_id' => 'required|uuid',
            'seller_id'=> 'required|uuid',
        ]);
        $escrow = $this->service->createEscrow($data);
        return response()->json($escrow, 201);
    }

    public function accept(Escrow $escrow, Request $request)
    {
        $adminId = $request->user('admin')->id;
        $escrow = $this->service->acceptEscrow($escrow, $adminId);
        return response()->json($escrow);
    }

    public function uploadBuyerSignature(Escrow $escrow, Request $request)
    {
        $file = $request->validate(['file' => 'required|file'])['file'];
        $escrow = $this->service->uploadBuyerSignature($escrow, $file);
        return response()->json($escrow);
    }

    public function uploadSellerSignature(Escrow $escrow, Request $request)
    {
        $file = $request->validate(['file' => 'required|file'])['file'];
        $escrow = $this->service->uploadSellerSignature($escrow, $file);
        return response()->json($escrow);
    }

    public function uploadReceipts(Escrow $escrow, Request $request)
    {
        $files = $request->validate(['files.*' => 'required|file'])['files'];
        $escrow = $this->service->uploadReceipts($escrow, $files);
        return response()->json($escrow);
    }

    public function confirmPayment(Escrow $escrow, Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);
        $escrow = $this->service->confirmPayment(
            $escrow,
            $data['amount'],
            PaymentMethod::from($data['method'])
        );
        return response()->json($escrow);
    }

    public function proposeSlots(Escrow $escrow, Request $request)
    {
        $data = $request->validate([
            'weekdays' => 'required|array',
            'weekdays.*' => 'in:' . implode(',', Weekday::values()),
            'times' => 'required|array',
            'times.*' => 'date_format:H:i',
        ]);
        $weekdays = array_map(static fn($v) => Weekday::from($v), $data['weekdays']);
        $slots = $this->service->proposeSlots($escrow, $weekdays, $data['times']);
        return response()->json($slots);
    }

    public function selectSlot(Escrow $escrow, Request $request)
    {
        $slotId = $request->validate(['slot_id' => 'required|exists:time_slots,id'])['slot_id'];
        $slot = $this->service->selectSlot($escrow, $slotId);
        return response()->json($slot);
    }

    public function rejectScheduling(Escrow $escrow)
    {
        $escrow = $this->service->rejectScheduling($escrow);
        return response()->json($escrow);
    }

    public function confirmDelivery(Escrow $escrow)
    {
        $escrow = $this->service->confirmDelivery($escrow);
        return response()->json($escrow);
    }

    public function releaseFunds(Escrow $escrow, Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'method' => 'required|in:' . implode(',', PaymentMethod::values()),
        ]);
        $escrow = $this->service->releaseFunds(
            $escrow,
            $data['amount'],
            PaymentMethod::from($data['method'])
        );
        return response()->json($escrow);
    }

    public function cancel(Escrow $escrow)
    {
        $escrow = $this->service->cancel($escrow);
        return response()->json($escrow);
    }

    public function refund(Escrow $escrow)
    {
        $escrow = $this->service->refund($escrow);
        return response()->json($escrow);
    }
}
