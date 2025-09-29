<div id="profile">
    <div class="row">
        <div class="col-sm-6 box">
            <h1>Member Details</h1>
            <table>
                <tr>
                    <td style="width: 7.1em;">Active:</td>
                    <td><span class="fa-lg fa fa-{{ $user->status ? 'check success' : 'remove danger' }}"></span></td>
                </tr>
                <tr>
                    <td>Committee:</td>
                    <td><span class="fa-lg fa fa-{{ $user->isCommittee() ? 'check success' : 'remove danger' }}"></span></td>
                </tr>
                <tr>
                    <td>Associate:</td>
                    <td><span class="fa-lg fa fa-{{ $user->isAssociate() ? 'check success' : 'remove danger' }}"></span></td>
                </tr>
                @if($user->tool_colours)
                    <tr>
                        <td>Tool Colours:</td>
                        <td>{!! $user->tool_colours_parsed !!}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="col-sm-6 box">
            <h1>Contact Details</h1>
            @if($user->show_email || $user->show_phone || $user->show_address)
                <table>
                    @if($user->show_email && $user->email)
                        <tr>
                            <td><span class="fa fa-envelope"></span></td>
                            <td>{!! link_to('mailto:' . $user->email, $user->email) !!}</td>
                        </tr>
                    @endif
                    @if($user->show_phone && $user->phone)
                        <tr>
                            <td><span class="fa fa-phone"></span></td>
                            <td>{{ $user->phone }}</td>
                        </tr>
                    @endif
                    @if($user->show_address && $user->address)
                        <tr>
                            <td><span class="fa fa-home"></span></td>
                            <td>{!! nl2br($user->address) !!}</td>
                        </tr>
                    @endif
                </table>
            @else
                <h4>{{ $user->forename }} hasn't shared any of their contact details</h4>
            @endif
        </div>
    </div>
</div>