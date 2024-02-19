@extends('emails.layout')

@section('title')
    {{ __('New message') }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-new-message', [
        'id' => $object->id,
        'subject' => is_a($object,'App\Models\Expense') ? $object->mission->subject : $object->subject,
        'author' => $author ]) }}.<br /><br />

    @include('emails.button', [
        'link' => $url,
        'text' => __('Show message') ])

    <br />

    {!! __('mail-ending') !!}
@endsection