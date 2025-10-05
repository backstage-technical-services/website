<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Package\Notifications\Facades\Notify;
use Package\SearchTools\SearchTools;

class UsersController extends Controller
{
    /**
     * Define the bulk actions.
     *
     * @var array
     */
    private static $BulkActions = [
        'archive' => 'Archive',
        'committee' => 'Make committee',
        'associate' => 'Make associate',
    ];

    /**
     * Define an array of the messages to show for the bulk actions.
     *
     * @var array
     */
    private static $BulkActionMap = [
        'archive' => 'archived',
        'committee' => 'given committee access',
        'associate' => 'made associates',
    ];

    /**
     * View the list of user accounts
     *
     * @param SearchTools $searchTools
     *
     * @return $this
     */
    public function index(SearchTools $searchTools)
    {
        $this->authorize('index', User::class);

        // Get the search or filter request
        $search = $searchTools->search();
        $filter = $searchTools->filter();

        // Start the query
        $users = User::nameOrder();

        // Filter / search results
        if ($filter == 'all') {
            $users = $users->get();
        } elseif ($filter == 'archived') {
            $users = $users->archived()->get();
        } elseif ($filter == 'active') {
            $users = $users->active()->get();
        } elseif (in_array($filter, ['member', 'committee', 'associate', 'staff'])) {
            $users = $users->inGroup($filter)->get();
        } else {
            if (!is_null($search) && $search) {
                $users->search($search);
            }

            // Paginate
            $users = $users->paginate(20);
            $this->checkPage($users);
        }

        // Set the filter options
        $searchTools->setFilterOptions([
            'all' => 'All users',
            'archived' => 'Archived',
            'active' => 'Active',
            'member' => 'Member',
            'committee' => 'Committee',
            'associate' => 'Associate',
            'staff' => 'Staff',
        ]);

        return view('users.index')->with('users', $users)->with('bulkActions', self::$BulkActions);
    }

    /**
     * Show the form to create new users.
     *
     * @return $this|Factory|View
     */
    public function create()
    {
        $this->authorize('create', User::class);
        return view('users.create');
    }

    /**
     * View the results of creating multiple users.
     *
     * @return $this|RedirectResponse
     */
    public function createSummary()
    {
        $this->authorize('create', User::class);
        $bulk_results = session()->pull('create_summary');

        if ($bulk_results) {
            return view('users.create_summary')->with('results', $bulk_results);
        } else {
            return redirect()->route('user.create');
        }
    }

    /**
     * Create the new user(s).
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $mode = $request->get('mode');

        if ($mode == 'single') {
            return $this->storeSingle($request);
        } elseif ($mode == 'bulk') {
            return $this->storeBulk($request);
        } else {
            return redirect()->route('user.create');
        }
    }

    /**
     * Process the create form for single user mode.
     *
     * @param Request $request
     *
     * @return $this|RedirectResponse
     */
    private function storeSingle(Request $request)
    {
        // Make the validator
        $fields = ['name', 'username', 'type'];
        $validator = validator(
            $request->only($fields),
            User::getValidationRules($fields),
            $this->storeValidationMessages(),
        );

        if (!$validator->fails()) {
            // Create the user
            User::create(clean($request->only($fields)));
            Notify::success('User created');
            return redirect()->route('user.index');
        } else {
            // Fail with the inputs and errors
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput($request->only('name', 'username', 'type', 'mode'));
        }
    }

    /**
     * Process the create form for bulk user mode.
     *
     * @param Request $request
     *
     * @return $this|RedirectResponse
     */
    private function storeBulk(Request $request)
    {
        $rules = User::getValidationRules('name', 'username', 'type');
        $messages = $this->storeValidationMessages();

        // Validate initially
        $validator = validator(
            $request->only('users', 'type'),
            [
                'type' => $rules['type'],
                'users' => 'required|regex:/^[a-z]+\s[a-z]+[,][a-z0-9_\.]+$/im',
            ],
            $messages + [
                'users.required' => 'Please enter the list of users to add',
                'users.regex' =>
                    'Please enter each user in the format specified in the <a href="#" data-toggle="modal" data-target="#modal" data-modal-template="help">help</a>.',
            ],
        );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->only('users', 'type', 'mode'));
        }

        // Sanitise each user input
        $user_details = array_map(function ($value) {
            return trim($value);
        }, explode(PHP_EOL, $request->get('users')));

        // Try each user
        $results = [];
        foreach ($user_details as $i => $user) {
            $data = ['type' => $request->get('type')];
            [$data['name'], $data['username']] = explode(',', $user);
            $validator = validator($data, $rules, $messages);
            if ($validator->fails()) {
                $results[$i] = [
                    'success' => false,
                    'username' => $data['username'] ?: $i,
                    'message' => implode(PHP_EOL, Arr::flatten($validator->messages()->getMessages())),
                ];
            } elseif ($user = User::create($data)) {
                $results[$i] = [
                    'success' => true,
                    'username' => $user->username ?: $i,
                    'message' => 'User created successfully',
                ];
            } else {
                $results[$i] = [
                    'success' => false,
                    'username' => $user->username ?: $i,
                    'message' => 'Something went wrong when adding this user. Consult the logs for more information.',
                ];
            }
        }

        return redirect()->route('user.create.summary')->with('create_summary', $results);
    }

    /**
     * Get the validation messages for creating a new user.
     *
     * @return array
     */
    private function storeValidationMessages()
    {
        return [
            'name.required' => 'Please enter the new user\'s name',
            'name.name' => 'Please enter their forename and surname',
            'username.required' => 'Please enter the new user\'s BUCS username',
            'username.regex' => 'Please enter just their username',
            'username.unique' => 'A user with that username already exists',
            'type.required' => 'Please select an account type',
            'type.in' => 'Please select one of the provided account types',
        ];
    }

    /**
     * View a user.
     *
     * @param $username
     *
     * @return RedirectResponse
     */
    public function view($username)
    {
        return redirect()->route('member.view', ['username' => $username]);
    }

    /**
     * View the form for editing a user account.
     *
     * @param $username
     *
     * @return $this
     */
    public function edit($username)
    {
        $this->authorize('update', User::class);
        $user = User::where('username', $username)->firstOrFail();

        return view('users.edit')->with('user', $user);
    }

    /**
     * Update the user.
     *
     * @param                          $username
     * @param Request                  $request
     *
     * @return RedirectResponse
     */
    public function update($username, Request $request)
    {
        $this->authorize('update', User::class);
        $user = User::where('username', $username)->firstOrFail();

        $action = $request->get('action');
        if ($action == 'save') {
            return $this->updateDetails($user, $request);
        } elseif ($action == 'archive' || $action == 'unarchive') {
            $this->updateStatus($user, $request);
        } elseif ($action == 'change-pic') {
            $this->updateAvatar($user, $request);
        } elseif ($action == 'remove-pic') {
            $this->updateRemoveAvatar($user);
        }

        return redirect()->route('user.edit', ['username' => $username]);
    }

    /**
     * Update the user's details.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return $this|RedirectResponse
     */
    private function updateDetails(User $user, Request $request)
    {
        // Set the fields to update
        $fields = [
            'name',
            'username',
            'email',
            'phone',
            'dob',
            'type',
            'show_email',
            'show_phone',
            'show_address',
            'show_age',
        ];
        if ($user->isActiveUser()) {
            unset($fields[array_search('username', $fields)]);
            unset($fields[array_search('type', $fields)]);
        }

        // Get the request data
        $data = $request->only($fields);
        $data['dob'] = $data['dob'] ?: null;
        $data['show_email'] = $request->has('show_email');
        $data['show_phone'] = $request->has('show_phone');
        $data['show_address'] = $request->has('show_address');
        $data['show_age'] = $request->has('show_age');

        // Make the validator
        $rules = User::getValidationRules($fields);
        $rules['email'] .= ',' . $user->id;
        if (!$user->isActiveUser()) {
            $rules['username'] .= ',' . $user->id;
        }
        $messages = User::getValidationMessages($fields);
        $validator = validator($data, $rules, $messages);

        // Validate
        if ($validator->fails()) {
            return redirect()->back()->withInput($data)->withErrors($validator);
        } elseif ($user->update(clean($data))) {
            Notify::success('User updated');
            return redirect()->route('user.index');
        } else {
            Notify::error('An error occurred');
            return redirect()->route('user.edit', $user->username);
        }
    }

    /**
     * Archive or unarchive the user account.
     *
     * @param User    $user
     * @param Request $request
     */
    private function updateStatus(User $user, Request $request)
    {
        if ($user->isActiveUser()) {
            Notify::warning('You can\'t modify the status of your own account.');
        } elseif ($request->get('action') == 'archive' && $user->update(['status' => false])) {
            Notify::success('User archived');
        } elseif ($request->get('action') == 'unarchive' && $user->update(['status' => true])) {
            Notify::success('User unarchived');
        } else {
            Notify::error('An error occurred');
        }
    }

    /**
     * Update the user's profile picture.
     *
     * @param User    $user
     * @param Request $request
     */
    private function updateAvatar(User $user, Request $request)
    {
        $file = $request->file('avatar');
        if (!$file) {
            Notify::warning('Please select an image to use');
        } else {
            $user->setAvatar($file);
            Notify::success('Profile picture changed');
        }
    }

    /**
     * Remove the user's profile picture.
     *
     * @param User $user
     */
    private function updateRemoveAvatar(User $user)
    {
        if ($user->hasAvatar()) {
            $path = base_path('public') . $user->getAvatarUrl();
            if (is_writeable($path)) {
                unlink($path);
                Notify::success('Profile picture removed');
            } else {
                Notify::error('Could not remove the profile picture');
            }
        }
    }

    /**
     * Process and perform the bulk user actions.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function postIndex(Request $request)
    {
        $this->authorize('update', User::class);

        // Archive single user
        if ($request->has('archive-user')) {
            $this->archiveUser($request->get('archive-user'));
        } elseif ($request->has('bulk')) {
            $this->updateBulk($request);
        }

        return redirect()->route('user.index');
    }

    /**
     * Archive a single user.
     *
     * @param $userId
     */
    private function archiveUser($userId)
    {
        $user = User::find($userId);
        if ($user && $user->archive()) {
            Notify::success('User successfully archived');
        } elseif (!$user) {
            Notify::error('Could not find the user to archive');
        }
    }

    /**
     * Process multiple users in bulk.
     *
     * @param Request $request
     */
    private function updateBulk(Request $request)
    {
        // Get the action and users
        $action = $request->get('bulk-action');
        $users = $request->get('users');

        // Validate
        if (!$action || !in_array($action, array_keys(self::$BulkActions))) {
            Notify::warning('Select a valid action');
            return;
        }
        if (!$users || !is_array($users) || count($users) == 0) {
            Notify::warning('Select some users');
            return;
        }

        // Process each user
        $success = 0;
        foreach ($users as $id) {
            $user = User::find($id);
            if (
                $user &&
                (($action == 'archive' && $user->archive()) ||
                    ($action == 'committee' && $user->makeCommittee()) ||
                    ($action == 'associate' && $user->makeAssociate()))
            ) {
                $success++;
            }
        }

        // Create the flash message
        if ($success == count($users)) {
            Notify::success(sprintf('All of the selected users were succesfully %s', self::$BulkActionMap[$action]));
        } elseif ($success > 0) {
            Notify::warning(
                sprintf(
                    '%s of the %s users were successfully %s',
                    $success,
                    count($users),
                    self::$BulkActionMap[$action],
                ),
            );
        } else {
            Notify::error(sprintf('None of the selected users could be %s', self::$BulkActionMap[$action]));
        }
    }
}
