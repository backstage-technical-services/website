@extends('app.main')

@section('title', 'Booking Terms')
@section('header-main', config('app.name'))
@section('header-sub', 'Terms and Conditions for the Provision of Services')

@section('content')
    {!! Markdown::convertToHtml(view('contact.book.terms')) !!}
@endsection
