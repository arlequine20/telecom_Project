@extends('layouts.app')

@section('title', 'Customers | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Customers')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-4">Customer List</h5>
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>SIM Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->full_name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ ucfirst($customer->status) }}</td>
                    <td>{{ $customer->simCards->count() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</div>
@endsection
