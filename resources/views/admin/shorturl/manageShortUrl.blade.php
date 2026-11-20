@extends('layouts.admin.app')
@section('title', 'Short URLs')
@section('pageTitle', 'Short URLs')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-inline">
                    <label class="mr-2" for="statusFilter">Status</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">All</option>
                        <option value="Y">Enabled</option>
                        <option value="N">Disabled</option>
                    </select>
                </div>
                @can('shorturl.create')
                    <a href="{{ route('admin.createShortUrl') }}" class="btn btn-primary">Add Short URL</a>
                @endcan
            </div>
            <div id="Msg"></div>
            <div class="table-responsive">
                <table class="table table-bordered" id="shortUrlDataTable" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Original URL</th>
                            <th>Creator</th>
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
                    <h5 class="modal-title">Delete Short URL</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">This record will be marked as deleted.</div>
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
                $('#shortUrlDataTable').DataTable({
                    processing: true,
                    serverSide: true,
                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],
                    pageLength: 10,
                    paging: true,
                    ordering: true,
                    ajax: {
                        url: "{{ route('admin.allShortUrls') }}",
                        data: function (d) {
                            d._token = "{{ csrf_token() }}";
                            d.status = $('#statusFilter').val();
                        },
                        error: function () {
                            $('#Msg').html('<div class="alert alert-danger">Unable to load records. Please refresh the page.</div>');
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'Title', name: 'short_urls.title' },
                        { data: 'Original', name: 'short_urls.original_url' },
                        { data: 'Creator', name: 'users.name' },
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

            $('#statusFilter').on('change', function () {
                $('#shortUrlDataTable').DataTable().destroy();
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
                    url: "{{ url('admin/short-urls') }}/" + deleteId + "/delete",
                    success: function (response) {
                        if (response == 'success') {
                            $('#deleteModal').modal('hide');
                            $('#Msg').html('<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>Short URL has been deleted successfully.</div>');
                            $('#shortUrlDataTable').DataTable().destroy();
                            createTable();
                        }
                    },
                    error: function () {
                        $('#deleteModal').modal('hide');
                        $('#Msg').html('<div class="alert alert-danger">Unable to delete this record.</div>');
                    }
                });
            });
        });
    </script>
@endpush
