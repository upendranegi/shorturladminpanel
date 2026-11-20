@extends('layouts.admin.app')
@section('title', 'Users')
@section('pageTitle', 'Users')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-inline">
                    @if (auth()->user()->hasRole('SuperAdmin'))
                        <label class="mr-2" for="roleFilter">Role</label>
                        <select id="roleFilter" class="form-control">
                            <option value="">All</option>
                            <option value="Admin">Admin</option>
                            <option value="Member">Member</option>
                        </select>
                    @endif
                </div>
                @can('user.invite')
                    <a href="{{ route('admin.inviteUser') }}" class="btn btn-primary">Invite User</a>
                @endcan
            </div>
            <div id="Msg"></div>
            <div class="table-responsive">
                <table class="table table-bordered" id="userDataTable" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Company</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">This user will be marked as deleted and will not be able to log in.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            var deleteId = 0;

            function createTable() {
                $('#userDataTable').DataTable({
                    processing: true,
                    serverSide: true,
                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],
                    pageLength: 10,
                    paging: true,
                    ordering: true,
                    order: [],
                    ajax: {
                        url: "{{ route('admin.allUsers') }}",
                        data: function (d) {
                            d._token = "{{ csrf_token() }}";
                            d.role = $('#roleFilter').length ? $('#roleFilter').val() : '';
                        },
                        error: function () {
                            $('#Msg').html('<div class="alert alert-danger">Unable to load records. Please refresh the page.</div>');
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'Name', name: 'users.name' },
                        { data: 'Email', name: 'users.email' },
                        { data: 'Role', name: 'roles.name' },
                        { data: 'Company', name: 'companies.company_name' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    language: {
                        emptyTable: 'No records found.',
                        zeroRecords: 'No matching records found.'
                    }
                });
            }

            createTable();

            $('#roleFilter').on('change', function () {
                $('#userDataTable').DataTable().destroy();
                createTable();
            });

            $(document).on('click', '.btn-delete', function () {
                deleteId = $(this).data('id');
            });

            $('#confirmDelete').on('click', function () {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: "{{ url('admin/users') }}/" + deleteId + "/delete",
                    success: function (response) {
                        if (response == 'success') {
                            $('#deleteModal').modal('hide');
                            $('#Msg').html('<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>User has been deleted successfully.</div>');
                            $('#userDataTable').DataTable().destroy();
                            createTable();
                        }
                    },
                    error: function (xhr) {
                        $('#deleteModal').modal('hide');
                        var message = xhr.status === 404 ? 'User account does not exist.' : 'Unable to delete this record.';
                        $('#Msg').html('<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>');
                    }
                });
            });
        });
    </script>
@endpush
