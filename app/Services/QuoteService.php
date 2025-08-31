<?php

namespace App\Services;

use App\Exceptions\ResourceNotCreatedException;
use App\Http\Requests\QuoteRequest;
use App\Models\Quote;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class QuoteService
{
    /**
     * Get a paginated list of quotes.
     *
     * @param int $numPerPage
     *
     * @return LengthAwarePaginator
     */
    public function getPaginatedQuotes(int $numPerPage): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator $quotes */
        $quotes = Quote::orderBy('created_at', 'DESC')->paginate($numPerPage);

        $page = $quotes->currentPage();
        $quotes->map(function (Quote $quote, $i) use ($numPerPage, $page) {
            $quote->num = ($page - 1) * $numPerPage + $i + 1;
        });

        return $quotes;
    }

    /**
     * Create a new quote.
     *
     * @param QuoteRequest $request
     *
     * @return Quote
     * @throws ResourceNotCreatedException
     */
    public function createQuote(QuoteRequest $request): Quote
    {
        $quote = new Quote([
            'culprit' => clean($request->get('culprit')),
            'quote' => clean($request->get('quote')),
            'date' => Carbon::createFromFormat('Y-m-d H:i:s', $request->get('date'))->addMinutes(
                (float) $request->header('TZ-OFFSET'),
            ),
            'added_by' => $request->user()->id,
        ]);

        if (!$quote->save()) {
            throw new ResourceNotCreatedException('Could not create quote');
        }

        Log::info("User {$request->user()->id} created quote with ID {$quote->id}");

        return $quote;
    }

    /**
     * Delete a quote from it's ID.
     *
     * @param int $quoteId
     *
     * @return void
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function deleteQuote(int $quoteId): void
    {
        if (!Quote::where('id', $quoteId)->exists()) {
            throw new ModelNotFoundException(sprintf('Could not find quote with ID %s', $quoteId));
        }

        $userId = request()->user()->id;
        Log::info("User $userId deleted quote with ID $quoteId");

        Quote::destroy($quoteId);
    }
}
