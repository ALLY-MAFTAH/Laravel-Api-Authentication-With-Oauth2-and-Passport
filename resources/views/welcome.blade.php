@extends('layouts.app')
@section('content')

        <div class="relative flex items-top justify-center min-h-screen bg-light-100  sm:items-center py-1 sm:pt-0">


                <div class="text-center">

                    <h1 style="font-size: 50px; color:rgb(44, 11, 189);">Base Api Authentication</h1>
                </div>

            </div>
        <div class="container">
            <div class="row">
                <div class="col-md col-md-offset-4">
                    @if ($errors->has('msg'))
                        <div class="alert alert-warning">
                            {{ $errors->first('msg') }}
                            <button type="button" data-dismiss="alert" aria-label="Close" class="close"><span
                                    aria-hidden="true">X</span></button>
                        </div>
                    @endif

                    <div class="panel panel-default">
                        <div class="panel-heading text-center text-success">Social Login </div>

                        <div class="panel-body">
                            <p class="lead text-center">Login using your social network account from one of following
                                providers</p>
                            <div class="text-center"><a href="{{ route('social.oauth', 'facebook') }}"
                                    class="btn btn-primary btn-block">
                                    Login with Facebook
                                </a><br><br></div>
                            <div class="text-center"><a href="{{ route('social.oauth', 'twitter') }}"
                                    class="btn btn-info btn-block">
                                    Login with Twitter
                                </a><br><br></div>
                            <div class="text-center"><a href="{{ route('social.oauth', 'google') }}"
                                    class="btn btn-danger btn-block">
                                    Login with Google
                                </a><br><br></div>
                            <div class="text-center"><a href="{{ route('social.oauth', 'github') }}"
                                    class="btn btn-dark btn-block">
                                    Login with Github
                                </a><br><br></div>
                            <hr>
                            <div class="text-center"><a href="{{ route('login') }}"
                                    class="btn btn-default btn-block text-primary">
                                    Login with Email
                                </a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        </div>

        @endsection
