@extends('app.main')

@section('title', 'About Us')
@section('header-main', 'About Us')

@section('content')
    <h2>What is Backstage?</h2>
    <p>
        Backstage Technical Services is a group of student volunteers who provide technical expertise to other Students'
        Union Societies and University departments. We provide support in areas such as event management, sound, lighting,
        video and stage management to over 60 events per year.
    </p>

    <h2>What types of event do we provide services for?</h2>
    <p>
        All of the big events (and most of the smaller events) are crewed by members of Backstage - all of the bands that
        appear in the Tub, the experience that is Freshers' Week, The Summer Ball, plays by Bath University Student Theatre
        (BUST), musicals by Bath University Student Musical Society (BUSMS) and many, many more.
    </p>
    <p>
        We also provide support for high profile events, such as the SU Blues awards, One Young World Bath and other
        corporate conferences.
    </p>
    <p>
        Backstage works both on- and off-campus providing lighting, sound, set design, pyrotechnic, stage management and
        event management expertise.
    </p>

    <h2>Training</h2>
    <p>
        Backstage run a training course which provides continuous training from basic to advanced level on a wide range of
        subjects. Our training is open (and completely free) to all members. We also offer teaching on-event through our
        mentorship programme, where an experienced member will teach you how to perform a crew role.
    </p>

    <h2>Industry</h2>
    <p>
        Backstage works closely with a variety of companies in the events and enterainment industry. Members have gone on to
        work for companies such as <a href="https://enlx.co.uk">Enlightened</a>,
        <a href="https://www.taittowers.com">TAIT</a>, <a href="https://www.ips.co.uk">IPS</a>,
        <a href="https://assemblyfestival.com">Assembly Festival</a>, <a href="https://www.thespaceuk.com">theSpaceUK</a>
        and <a href="https://pmygroup.com">PMY Group</a>.
    </p>

    <h2>Booking</h2>
    <p>You can submit a booking for Backstage using the <a href="{{ route('contact.book') }}">online booking form</a>.</p>
@endsection
