@extends('layouts.app', [
    'title' => 'Universal Clipboard App',
    'description' => 'The Universal Clipboard App makes it simple to copy text between your PCs and from your phone to your PC or vice versa.'
])

@section('content')
    <h1>Universal Clipboard App</h1>

    <p>The Universal Clipboard App allows you to copy text between your PCs, and your Android phone or vice versa.</p>
    <p>We are currently in <em>closed beta</em>. Check back again later to sign up for an Universal Clipboard App account.</p>

    <p>Read the <a href="{{ route('pages.privacy-policy') }}">privacy policy</a> to learn how your data is stored.</p>
@endsection
