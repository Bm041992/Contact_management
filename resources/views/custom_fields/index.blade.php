<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Fields</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        label.error{
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Custom Fields</h2>
        <div class='mb-3 text-end'>
            <button class="btn btn-primary" id="btn-add">Add Custom Field</button>
            <a href='{{ url("/") }}' class="btn btn-primary ">Contacts</a>
        </div>
        <table id="customFieldsTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</body>
</html>
<!-- Reused Modal -->
<div class="modal fade" id="fieldModal" tabindex="-1" data-bs-backdrop='static'>
    <div class="modal-dialog">
        <form id="field-form" class="modal-content">
        <input type="hidden" name="id" id="field-id">
        <div class="modal-header">
            <h5 class="modal-title" id="fieldModalLabel">Add Custom Field</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class='mb-3'>
                <label>Field Name <span class='text-danger'>*</span></label>
                <input name="name" autocomplete='off' id="field-name" class="form-control mb-2" placeholder="Field Name" required>
            </div>
            <div class='mb-3'>
                <label>Select Field Type <span class='text-danger'>*</span></label>
                <select name="field_type" id="field-type" class="form-control mb-2">
                    <option value="">Select Field Type</option>
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                </select>
            </div>
            <div class='mb-3'>
                <label><input type="checkbox" name="is_required" id="field-required" value='1'> Required</label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" id="field-submit-btn">Save</button>
        </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(function(){
        $.ajaxSetup({ 
            headers:{ 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
            } 
        });

        const table = $('#customFieldsTable').DataTable({
            processing: true, 
            serverSide: true,
            order:[],
            ajax: "{{ route('custom-fields.index') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'field_type', name: 'field_type' },
                { 
                    data: 'is_required', name: 'is_required',
                    render: d => d ? 'Yes':'No'
                },
                { data: 'action', name: 'action',className:'dt-center', orderable: false, searchable: false }
            ]
        });

        $('#btn-add').on('click', ()=> {
            $('#field-form')[0].reset();
            $('#field-id').val('');
            $('#fieldModalLabel').text('Add Custom Field');
            $('#field-submit-btn').text('Save');
            $('#fieldModal').modal('show');
        });

        $(document).on('click', '.btn-edit', function () {
            const id = $(this).data('id');

            $.ajax({
                url: "{{ route('custom-fields.edit') }}",
                type: 'POST',
                data:{ id:id },
                dataType: 'json',
                beforeSend:function(){
                    $("body").LoadingOverlay("show");
                },
                success: function (res) 
                {
                    $("body").LoadingOverlay("hide");
                    if (res.success) 
                    {
                        const field = res.msg;
                        $('#field-id').val(field.id);
                        $('#field-name').val(field.name);
                        $('#field-type').val(field.field_type);
                        $('#field-required').prop('checked', field.is_required);

                        $('#fieldModalLabel').text('Edit Custom Field');
                        $('#field-submit-btn').text('Update');
                        $('#fieldModal').modal('show');
                    }
                    else
                    {
                        toastr.error(res.msg);
                    }
                },
                error: function () {
                    $("body").LoadingOverlay("hide");
                    toastr.error('Failed to load custom field data.');
                }
            });
        });

        $('#field-form').validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 255
                },
                field_type: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Field name is required",
                    maxlength: "Maximum 255 characters"
                },
                field_type: {
                    required: "Please select a field type"
                }
            },
            submitHandler: function (form) {
                const id = $('#field-id').val();
                const url = id ? "{{ route('custom-fields.update', ':id') }}".replace(':id', id) : "{{ route('custom-fields.store') }}";
                const data = $(form).serialize() + (id ? '&_method=PUT' : '');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    beforeSend:function(){
                        $("body").LoadingOverlay("show");
                    },
                    success: function (res) {
                        $("body").LoadingOverlay("hide");
                        if (res.success) {
                            $('#fieldModal').modal('hide');
                            table.ajax.reload(null, false);
                            toastr.success(res.msg);
                        }
                        else {
                            toastr.error(res.msg);
                        }
                    },
                    error: function (xhr) {
                        $("body").LoadingOverlay("hide");
                        toastr.error('Something went wrong!');
                    }
                });
            }
        });

        $(document).on('click', '.btn-delete', function () {
            if (!confirm('Are you sure you want to delete this custom field?')) return;

            const id = $(this).data('id');

            $.ajax({
                url: "{{ route('custom-fields.destroy') }}",
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                beforeSend:function(){
                    $("body").LoadingOverlay("show");
                },
                success: function (res) 
                {
                    $("body").LoadingOverlay("hide");
                    if (res.success) 
                    {
                        toastr.success(res.msg);
                        table.ajax.reload(null, false); 
                    } 
                    else 
                    {
                        toastr.error('Delete failed.');
                    }
                },
                error: function (xhr) {
                    $("body").LoadingOverlay("hide");
                    toastr.error('Something went wrong! An error occurred while deleting the field.');
                }
            });
        });

    });
</script>