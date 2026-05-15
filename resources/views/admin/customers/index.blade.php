@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-people"></i> Customers</h1>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}"></div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->email }}</td>
                            <td>{{ $c->phone }}</td>
                            <td><span class="badge bg-info">{{ $c->orders_count }}</span></td>
                            <td><span class="badge {{ $c->is_active ? 'bg-success' : 'bg-danger' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" style="display:inline;" onsubmit="return confirm('Delete?');"><@csrf <@method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">No customers</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $customers->links() }}</div>
</div>
@endsection