@extends('layouts.back')
@section('title','Permissions - Change Management Form')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit Permissions</h6>
                    <p>Edit permission dengan memperbaharui form di bawah ini.</p>
                </div>
                <div class="card-body">
                    <form action="{{route('permissions.update',['permission' => $permission->id])}}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" placeholder="Nama Permission" value="{{old('name') ?? $permission->name}}">
                            <label for="name">Nama Permission</label>
                            @error('name')
                            <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('guard_name') is-invalid @enderror" name="guard_name" id="guard_name" placeholder="Guard Name" value="{{old('guard_name') ?? $permission->guard_name}}">
                            <label for="guard_name">Guard Name</label>
                            @error('guard_name')
                            <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-dark">Edit Permission</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
