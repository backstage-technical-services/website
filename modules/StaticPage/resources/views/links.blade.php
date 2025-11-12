@extends('app.main')

@section('title', 'Links')
@section('header-main', 'Links')

@section('content')
    <p>
        This page contains links to many other web resources, divided by category. Please note that these sites are not
        associated with BTS unless otherwise stated. If you would like a link to be added then please
        <a href="{{ route('contact.enquiries') }}">contact us</a>.
    </p>

    <h3 id="lighting-manufacturers">Lighting Manufacturers</h3>
    <ul>
        <li><a href="http://www.etcconnect.com/">ETC</a></li>
        <li><a href="http://www.zero88.co.uk/">Zero88</a></li>
        <li><a href="http://www.avolites.org.uk/">Avolites</a></li>
        <li><a href="http://www.martin.com/">Martin Professional</a></li>
        <li><a href="http://www.highend.com/">Highend Systems</a></li>
        <li><a href="http://www.pulsarlight.com/">Pulsar Lighting</a></li>
        <li><a href="http://www.robe.cz/">Robe Show Lighting</a></li>
        <li><a href="http://www.futurelight.co.uk/">Futurelight</a></li>
    </ul>

    <h3 id="sound-manufacturers">Sound Manufacturers</h3>
    <ul>
        <li><a href="http://www.soundcraft.com/">Soundcraft</a></li>
        <li><a href="http://www.allen-heath.com/">Alien &amp; Heath</a> (formely AHB)</li>
        <li><a href="http://www.mc2-audio.co.uk/">MC2 Amplifiers</a></li>
        <li><a href="http://www.behringer.com/">Behringer</a></li>
        <li><a href="http://www.global.yamaha.com/products/pa/index.html">Yamaha</a></li>
        <li><a href="http://www.audient.co.uk/">Audient</a></li>
    </ul>

    <h3 id="discussion-boards-and-forums">Discussion Boards and Forums</h3>
    <ul>
        <li><a href="http://www.blue-room.org.uk/">The Blue Room</a> - A UK based privately-run forum</li>
        <li><a href="http://www.lightnetwork.com/">The Light Network</a> - A largely US forum with a large manufacturer
            presence</li>
    </ul>

    <h3 id="professional-bodies">Professional Bodies</h3>
    <ul>
        <li><a href="http://www.abtt.org.uk/">ABTT</a> - The Association of British Theatre Technicians</li>
        <li><a href="http://www.plasa.org/">PLASA</a> - The Professional Lighting and Sound Association</li>
        <li><a href="http://www.ald.org.uk/">ALD</a> - The Association of Lighting Designers (a UK body)</li>
        <li><a href="http://www.psa.org.uk/">PSA</a> - The Production Services Association</li>
    </ul>

    <h3 id="lighting-suppliers">Lighting Suppliers</h3>
    <ul>
        <li><a href="http://www.enlightenedlighting.co.uk/">Enlightened Lighting</a> - A Bath-based lighting hire company
        </li>
        <li><a href="http://www.stagedepot.co.uk/?q=rb/29/31/33">Stage Depot</a> - A Bath-based sales company</li>
    </ul>

    <h3 id="sound-equipment-suppliers">Sound Equipment Suppliers</h3>
    <ul>
        <li><a href="http://www.apraudio.com/">APR Audio</a></li>
        <li><a href="http://www.audioforum.co.uk/">Audio Forum</a></li>
    </ul>

    <h3 id="other-useful-links">Other Useful Links</h3>
    <ul>
        <li><a href="http://www.nsdf.org.uk/">NSDF</a> - The National Student Drama Festival</li>
        <li><a href="http://www.nlfireworks.com/">Northern Lights</a> - Norther Lights Fireworks Company</li>
        <li><a href="http://www.modelbox.co.uk/">Modelbox</a> - CAD libraries and models of performance spaces</li>
    </ul>
@endsection
