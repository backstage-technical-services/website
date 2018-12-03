<?php

namespace App\Services;

use App\Exceptions\ResourceNotCreatedException;
use App\Http\Requests\QuoteRequest;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuoteService
{
    /**
     * Get a paginated list of quotes.
     *
     * @param $numPerPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedQuotes($numPerPage)
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $quotes */
        $quotes = Quote::orderBy('created_at', 'DESC')
                       ->paginate($numPerPage);

        $page = $quotes->currentPage();
        $quotes->map(function (Quote $quote, $i) use ($numPerPage, $page) {
            $quote->num = ($page - 1) * $numPerPage + $i + 1;
        });

        return $quotes;
    }

    /**
     * Create a new quote.
     *
     * @param \App\Http\Requests\QuoteRequest $request
     *
     * @return \App\Models\Quote
     * @throws \App\Exceptions\ResourceNotCreatedException
     */
    public function createQuote(QuoteRequest $request)
    {
        $quote = new Quote([
            'culprit'  => clean($request->get('culprit')),
            'quote'    => clean($request->get('quote')),
            'date'     => Carbon::createFromFormat('Y-m-d H:i:s', $request->get('date'))->addMinutes($request->header('TZ-OFFSET')),
            'added_by' => $request->user()->id,
        ]);

        if (!$quote->save()) {
            throw new ResourceNotCreatedException('Could not create quote');
        }

        return $quote;
    }

    /**
     * Delete a quote from it's ID.
     *
     * @param int $quoteId
     *
     * @return void
     * @throws \Exception
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteQuote($quoteId)
    {
        if (!Quote::where('id', $quoteId)->exists()) {
            throw new ModelNotFoundException(sprintf("Could not find quote with ID %s", $quoteId));
        }

        Quote::destroy($quoteId);
    }
}