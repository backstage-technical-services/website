<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Services\QuoteService;
use bnjns\LaravelNotifications\NotificationHandler;

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
     * @param \App\Services\QuoteService                      $service
     * @param \bnjns\LaravelNotifications\NotificationHandler $notify
     */
    public function __construct(QuoteService $service, NotificationHandler $notify)
    {
        $this->middleware('auth');
        $this->service = $service;
        $this->notify  = $notify;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $quotes = $this->service->getPaginatedQuotes(15);

        return view('quotes.index')->with(compact('quotes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\QuoteRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ResourceNotCreatedException
     */
    public function store(QuoteRequest $request)
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
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function delete($id)
    {
        $this->service->deleteQuote($id);

        $this->notify->success('Quote deleted');
        return response()->json(true);
    }
}
