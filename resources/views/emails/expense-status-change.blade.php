@extends('emails.layout')

@section('title')
    {{ __('Expenses related to mission').' '.__($status) }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-expenses-'.$status, [
        'id' => $mission->id,
        'subject' => $mission->subject,
        'manager' => $manager ]) }}.<br /><br />

    @include('emails.button', [
        'link' => route('edit-expense', [$mission, $mission->expense]),
        'text' => __('Show my expenses') ])

    <br />

    {!! __('mail-ending') !!}
@endsection