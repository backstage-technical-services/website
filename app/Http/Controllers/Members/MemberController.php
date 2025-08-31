<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Package\Notifications\Facades\Notify;
use Package\SearchTools\SearchTools;

class MemberController extends Controller
{
    /**
     * Require that the user is logged in for all methods.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the members dashboard.
     *
     * @return RedirectResponse
     */
    public function dash()
    {
        if (auth()->user()->isMember()) {
            return $this->dashMember();
        } else {
            return $this->dashStaff();
        }
    }

    /**
     * View the members dashboard.
     *
     * @return View
     */
    private function dashMember()
    {
        return view('members.dash')->with([
            'events' => Event::type(Event::TYPE_EVENT)->future()->oldestFirst()->limit(5)->get(),
            'training' => Event::type(Event::TYPE_TRAINING)->future()->oldestFirst()->limit(5)->get(),
            'socials' => Event::type(Event::TYPE_SOCIAL)->future()->oldestFirst()->limit(5)->get(),
            'need_tem' => Event::type(Event::TYPE_EVENT)->future()->oldestFirst()->whereNull('em_id')->get(),
            'paperwork' => Event::type(Event::TYPE_EVENT)
                ->where('em_id', auth()->user()->id)
                ->whereNested(function ($query) {
                    $query
                        ->where('paperwork', 'like', '%"risk_assessment":false%')
                        ->orWhere('paperwork', 'like', '%"insurance":false%')
                        ->orWhere('paperwork', 'like', '%"finance_em":false%')
                        ->orWhere('paperwork', 'like', '%"event_report":false%');
                })
                ->get(),
        ]);
    }

    /**
     * The SU dashboard is currently only a redirect to the events diary.
     *
     * @return RedirectResponse
     */
    private function dashStaff()
    {
        return redirect()->route('event.diary');
    }

    /**
     * View a member's profile.
     *
     * @param        $username
     * @param string $tab
     *
     * @return $this
     */
    public function view($username, $tab = 'profile')
    {
        $user = User::where('username', $username)->firstOrFail();

        return view('members.view')->with([
            'user' => $user,
            'tab' => $tab,
        ]);
    }

    /**
     * View your profile.
     *
     * @param string  $tab
     * @param Request $request
     *
     * @return $this
     */
    public function profile(Request $request, $tab = 'profile')
    {
        return view('members.view')->with([
            'user' => $request->user(),
            'tab' => $tab,
        ]);
    }

    /**
     * Update the member's profile.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $this->requireAjax();

        $update_action = $request->get('update');
        $remove_action = $request->get('remove');

        if ($update_action == 'personal') {
            return $this->updatePersonal($request);
        } elseif ($update_action == 'contact') {
            return $this->updateContact($request);
        } elseif ($update_action == 'avatar') {
            return $this->updateAvatar($request);
        } elseif ($remove_action == 'avatar') {
            return $this->removeAvatar($request->user());
        } elseif ($update_action == 'password') {
            return $this->updatePassword($request);
        } elseif ($update_action == 'privacy') {
            return $this->updatePrivacy($request);
        } elseif ($update_action == 'other') {
            return $this->updateOther($request);
        } elseif ($update_action == 'diary-preferences') {
            return $this->updateDiaryPreferences($request);
        } else {
            return $this->ajaxError(404, 404, 'Unknown action');
        }
    }

    /**
     * Update the member's personal details
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updatePersonal(Request $request)
    {
        $this->updateMemberFields($request, ['name', 'nickname', 'dob']);
        Notify::success('Changes saved');
        return $this->ajaxResponse('Changes saved');
    }

    /**
     * Update the member's contact details.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updateContact(Request $request)
    {
        $this->updateMemberFields($request, ['email', 'phone', 'address']);
        Notify::success('Changes saved');
        return $this->ajaxResponse('Changes saved');
    }

    /**
     * Update the member's profile picture.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updateAvatar(Request $request)
    {
        $this->authorizeGate('member');

        $this->validate(
            $request,
            [
                'avatar' => 'required|file',
            ],
            [
                'avatar.required' => 'Please select an image to use',
                'avatar.file' => 'Please select an image to use',
            ],
        );

        $request->user()->setAvatar($request->file('avatar'));
        Notify::success('Profile picture changed');
        return $this->ajaxResponse('Profile picture changed');
    }

    /**
     * Remove the member's avatar.
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    private function removeAvatar(User $user)
    {
        $this->authorizeGate('member');
        if ($user->hasAvatar()) {
            $path = base_path('public') . $user->getAvatarUrl();
            if (is_writeable($path)) {
                unlink($path);
                Notify::success('Profile picture removed');
                return $this->ajaxResponse('Profile picture removed');
            } else {
                return $this->ajaxError(0, 422, 'Could not remove your profile picture');
            }
        }
    }

    /**
     * Update the member's password.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updatePassword(Request $request)
    {
        // Validate the request
        $validator = validator(
            $request->only('password', 'password_new', 'password_confirm'),
            [
                'password' => 'required',
                'password_new' => 'required|min:5',
                'password_confirm' => 'required|same:password_new',
            ],
            [
                'password.required' => 'Please enter your current password',
                'password_new.required' => 'Please enter your new password',
                'password_new.min' => 'Please use at least 5 characters',
                'password_confirm.required' => 'Please confirm your password',
                'password_confirm.same' => 'Your new passwords don\'t match',
            ],
        );
        // Add the check for the current password
        $validator->after(function ($validator) use ($request) {
            $check = auth()->validate([
                'email' => $request->user()->email,
                'password' => $request->get('password'),
            ]);
            if (!$check) {
                $validator->errors()->add('password', 'Your current password is incorrect');
            }
        });
        $this->validateWith($validator);

        // Update
        $request->user()->update([
            'password' => bcrypt($request->get('password_new')),
        ]);
        Notify::success('Password updated');
        return $this->ajaxResponse('Password updated');
    }

    /**
     * Update the member's privacy settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updatePrivacy(Request $request)
    {
        $this->authorizeGate('member');

        // Get the request data
        $data = [
            'show_email' => $request->has('show_email'),
            'show_phone' => $request->has('show_phone'),
            'show_address' => $request->has('show_address'),
            'show_age' => $request->has('show_age'),
        ];

        // Validate
        $fields = ['show_email', 'show_phone', 'show_address', 'show_age'];
        $rules = User::getValidationRules($fields);
        $messages = User::getValidationMessages($fields);
        $this->validate($request, $rules, $messages);

        // Update
        $request->user()->update($data);

        Notify::success('Privacy settings updated');
        return $this->ajaxResponse('Privacy settings updated');
    }

    /**
     * Update the member's diary preferences.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updateDiaryPreferences(Request $request)
    {
        // Authorise
        $this->authorizeGate('member');

        // Validate the request
        $event_types = $request->get('event_types');
        $crewing = $request->get('crewing');

        if (!is_array($event_types) || count($event_types) == 0 || !in_array($crewing, ['*', 'true'])) {
            return $this->ajaxError(422, 422, 'Your preferences could not be saved.');
        }

        // Update
        $user = $request->user();
        $diary_preferences = $user->diary_preferences;
        $diary_preferences['event_types'] = $request->get('event_types');
        $diary_preferences['crewing'] = $request->get('crewing');
        $user->diary_preferences = $diary_preferences;

        return $user->save() ? $this->ajaxResponse('Saved.') : $this->ajaxError(500, 500, 'An unknown error occurred.');
    }

    /**
     * Update the member's other settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    private function updateOther(Request $request)
    {
        // Update the default fields
        $this->authorizeGate('member');
        $this->updateMemberFields($request, ['tool_colours']);

        // Process the event export settings
        if ($request->has('event_export') != $request->user()->hasExportToken()) {
            $request->user()->export_token = $request->has('event_export') ? Str::random(100) : null;
            $request->user()->save();
        }

        // Messages
        Notify::success('Changes saved');
        return $this->ajaxResponse('Changes saved');
    }

    /**
     * Validate and update a list of fields.
     *
     * @param Request $request
     * @param array   $fields
     *
     * @return mixed
     */
    private function updateMemberFields(Request $request, array $fields)
    {
        // Set up the validation
        $rules = User::getValidationRules($fields);
        $messages = User::getValidationMessages($fields);

        // If validating the user's email, allow their current email address.
        if (isset($rules['email'])) {
            $rules['email'] .= ',' . $request->user()->id;
        }

        // Validate
        $this->validate($request, $rules, $messages);

        // Update
        return $request->user()->update(clean($request->only($fields)));
    }

    /**
     * View the membership
     *
     * @param SearchTools $searchTools
     *
     * @return $this
     */
    public function membership(SearchTools $searchTools)
    {
        // Begin the query
        $members = User::active()->member()->orderBy('surname', 'ASC')->orderBy('forename', 'ASC');

        // Apply the search, if exists
        if ($searchTools->search()) {
            $members->search($searchTools->search());
        }

        return view('members.membership')->with('members', $members->get());
    }
}
