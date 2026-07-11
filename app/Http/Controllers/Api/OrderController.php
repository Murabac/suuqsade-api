<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BatchOrderRequest;
use App\Http\Requests\Api\PaymentSentRequest;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Requests\Api\UpdateProductNoteRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ProductMetadataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filter = $request->query('filter', 'all');

        $query = $request->user()->orders()->latest();

        $query = match ($filter) {
            'active' => $query->whereNotIn('status', [
                OrderStatus::Delivered->value,
                OrderStatus::Cancelled->value,
            ]),
            'delivered' => $query->where('status', OrderStatus::Delivered->value),
            default => $query,
        };

        return OrderResource::collection($query->paginate(20));
    }

    public function store(StoreOrderRequest $request, OrderService $orders): JsonResponse
    {
        $order = $orders->create(
            $request->user(),
            $request->validated('product_link'),
            $request->validated('product_note'),
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function storeBatch(BatchOrderRequest $request, OrderService $orders): JsonResponse
    {
        $created = $orders->createBatch(
            $request->user(),
            $request->validated('links'),
            $request->validated('notes') ?? [],
        );

        return OrderResource::collection(collect($created))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Order $order, ProductMetadataService $metadata): OrderDetailResource
    {
        $this->authorize('view', $order);

        $order = $metadata->fetchForOrder($order);
        $order->load(['statusHistory' => fn ($q) => $q->orderBy('created_at'), 'payments']);

        return new OrderDetailResource($order);
    }

    public function paymentSent(PaymentSentRequest $request, Order $order, OrderService $orders): OrderResource|JsonResponse
    {
        $this->authorize('update', $order);

        try {
            $updated = $orders->markPaymentSent($order, $request->validated('method'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($updated);
    }

    public function updateVariant(UpdateProductNoteRequest $request, Order $order, OrderService $orders): OrderResource|JsonResponse
    {
        $this->authorize('update', $order);

        try {
            $updated = $orders->updateProductNote($order, $request->validated('product_note'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($updated);
    }
}
