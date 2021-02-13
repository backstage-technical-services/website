<?php

namespace App\Http\Controllers;

use App\Exceptions\ResourceNotCreatedException;
use App\Http\Requests\QuoteRequest;
use App\Services\QuoteService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Package\Notifications\NotificationHandler;

class QuoteController extends Controller
{
    /**
     * @var QuoteService
     */
    private $service;

    /**
     * @var NotificationHandler
     */
    private $notify;

    /**
     * QuoteController constructor.
     *
     * @param QuoteService $service
     * @param NotificationHandler $notify
     */
    public function __construct(QuoteService $service, NotificationHandler $notify)
    {
        $this->middleware('auth');
        $this->service = $service;
        $this->notify = $notify;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $quotes = $this->service->getPaginatedQuotes(15);

        return view('quotes.index')->with(compact('quotes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QuoteRequest $request
     *
     * @return JsonResponse
     * @throws ResourceNotCreatedException
     */
    public function store(QuoteRequest $request): JsonResponse
    {
        $quote = $this->service->createQuote($request);

        $this->notify->success('Quote created');
        return response()->json($quote);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(int $id): JsonResponse
    {
        $this->service->deleteQuote($id);

        $this->notify->success('Quote deleted');
        return response()->json(true);
    }
}
