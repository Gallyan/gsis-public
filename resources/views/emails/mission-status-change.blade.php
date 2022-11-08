@extends('emails.layout')

@section('title')
    {{ __('Mission').' '.__($status) }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-mission-'.$status, [
        'id' => $mission->id,
        'subject' => $mission->subject,
        'manager' => $manager ]) }}.<br /><br />

    @include('emails.button', [
        'link' => route('edit-mission', $mission),
        'text' => __('Show my mission') ])

    <br />

    {!! __('mail-ending') !!}
@endsection