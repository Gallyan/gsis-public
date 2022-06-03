@extends('emails.layout')

@section('title')
    {{ __('You\'ve got new message') }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-new-message', [
        'id' => $object->id,
        'subject' => $object->subject,
        'manager' => $manager ]) }}.<br /><br />

    @include('emails.button', [
        'link' => $url,
        'text' => __('Show message') ])

    <br />

    {!! __('mail-ending') !!}
@endsection