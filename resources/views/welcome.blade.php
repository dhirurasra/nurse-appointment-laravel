@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4 justify-content-center">
            <div class="col-md-8">
                <h1>Book your appointment today!</h1>
                <h4>Use these crediential to test the app:</h4>
                <p>Admin--email: admin@gmail.com, password: password</p>
                <p>Patient--email: patient@gmail.com, password: password</p>
                @guest
                    <div class="mt-5">
                        <a href="{{ url('/register') }}"> <button class="btn btn-primary">Register as Patient</button></a>
                        <a href="{{ url('/login') }}"><button class="btn btn-success">Login</button></a>
                    </div>
                @endguest
            </div>
        </div>

        {{-- Input --}}
        <form action="{{ url('/') }}" method="GET">
            <div class="card">
                <div class="card-body">
                    <div class="card-header">Find Nurses</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-8">
                                <input type="date" name='date' id="datepicker" class='form-control'>
                            </div>
                            <div class="col-md-6 col-sm-4">
                                <button class="btn btn-primary" type="submit">Go</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Display nurses --}}
        <div class="card">
            <div class="card-body">
                <div class="card-header">List of Nurses Available: @isset($formatDate) {{ $formatDate }}
                    @endisset
                </div>
                <div class="card-body table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Expertise</th>
                                <th>Book</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nurses as $key=>$nurse)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><img src="{{ asset('images') }}/{{ $nurse->nurse->image }}" alt="nurse photo"
                                            width="100px"></td>
                                    <td>{{ $nurse->nurse->name }}</td>
                                    <td>{{ $nurse->nurse->department }}</td>
                                    @if (Auth::check() && auth()->user()->role->name == 'patient')
                                        <td>
                                            <a href="{{ route('create.appointment', [$nurse->user_id, $nurse->date]) }}"><button
                                                    class="btn btn-primary">Appointment</button></a>
                                        </td>
                                    @else
                                        <td>For patients ONLY</td>
                                    @endif
                                </tr>
                            @empty
                                <td>No nurses available</td>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
