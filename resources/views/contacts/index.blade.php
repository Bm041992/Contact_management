<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contacts</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>label.error{color:red}</style>
</head>
<body>
    <div class="container mt-5">
        <h2>Contacts</h2>
        <div class='mb-3 text-end'>
            <button name='add_contact' class="btn btn-primary " id="btn-add">Add Contact</button>
            <a href='{{ url("custom-fields") }}' class="btn btn-primary ">Add Custom Fields</a>
            <button name='merge_contact' class="btn btn-primary" id="btn-merge-contact">Merge Contact</button>
        </div>
        <table id="contactsTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Custom Fields</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</body>
</html>
<!-- Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" data-bs-backdrop='static'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="contact-form" method='post' class="modal-content" enctype="multipart/form-data">
                <input type="hidden" id="contact-id" name="id">
                
                <div class="modal-body">
                    <div class="mb-3 alert alert-danger d-none" id='contact_form_error'>
                    </div>
                    <div class="mb-2">
                        <label>Name <span class='text-danger'>*</span></label>
                        <input type='text' name="name" id="contact-name" class="form-control" placeholder="Name" autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label d-block">Gender</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male">
                            <label class="form-check-label" for="genderMale">Male</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female">
                            <label class="form-check-label" for="genderFemale">Female</label>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" class="form-control">
                        <div id="profile-image-preview" class="mt-1"></div>
                    </div>
                    <div class="mb-2">
                        <label>Additional File</label>
                        <input type="file" name="additional_file" class="form-control">
                        <div id="additional-file-preview" class="mt-1"></div>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <div class="mb-2 row">
                            <div class="col-md-8">
                                <input type='text' name="email[]" class="form-control contact_email" placeholder="Email" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <button type="button" name='add_email_fields' class="btn btn-primary add_email_fields">+</button>
                            </div>
                        </div>
                        <div id="email-fields"></div>
                    </div>
                    <div class="mb-2">
                        <label>Phone <span class='text-danger'>*</span></label>
                        <div class="mb-2 row">
                            <div class="col-md-8">
                                <input type='text' name="phone[]" class="form-control contact_phone" placeholder="Phone" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <button type="button" name='add_phone_fields' class="btn btn-primary add_phone_fields">+</button>
                            </div>
                        </div>
                        <div id="phone-fields"></div>
                    </div>
                    
                    <div id="custom-fields-section" class="mb-2"></div>
                </div>
                <div class="modal-footer">
                    <button name='submit_contact' class="btn btn-success" id="contact-submit-btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="merge_contact_modal" data-bs-backdrop='static'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merge Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="merge-contact-form" method='post'>
                <div class="modal-body">
                    <div class="alert alert-info mb-3" role="alert">
                        <strong>Alert:</strong> The master contact will remain unchanged. 
                        Emails & phone numbers field values from the secondary contact 
                        will be added to the master contact. 
                        The secondary contact will be deactivated.
                    </div>
                    <div class="mb-3 alert alert-danger d-none" id='merge_contact_error'>
                    </div>
                    <div class="mb-3">
                        <label>Master Contact <span class='text-danger'>*</span></label>
                        <select name='master_contact_id' id='master_contact' class='form-control'>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Secondary Contact <span class='text-danger'>*</span></label>
                        <select name='secondary_contact_id' id='secondary_contact' class='form-control'>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button name='submit_merge_contact' class="btn btn-success" id="merge-contact-submit-btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>

$.validator.addMethod('filesize', function(value, element, param) {
		// param = size (en bytes) 
		// element = element to validate (<input>)
		// value = value of the element (file name)
    return this.optional(element) || (element.files[0].size <= param); 

}, "File Size must be less than or equal to 2Mb");

jQuery.validator.addMethod("uniqueEmail", function(value, element) {
    let isDuplicate = false;
    let currentVal = value.trim().toLowerCase();
    let seen = [];

    $('.contact_email').each(function() {
        let val = $(this).val().trim().toLowerCase();
        if (val !== '') {
            if (seen.includes(val)) {
                if (val === currentVal && element === this) {
                    isDuplicate = true;
                }
            }
            seen.push(val);
        }
    });

    return !isDuplicate;
}, "Duplicate emails are not allowed");

jQuery.validator.addMethod("uniquePhone", function(value, element) {
    let isDuplicate = false;
    let currentVal = value.trim().toLowerCase();
    let seen = [];

    $('.contact_phone').each(function() {
        let val = $(this).val().trim().toLowerCase();
        if (val !== '') {
            if (seen.includes(val)) {
                if (val === currentVal && element === this) {
                    isDuplicate = true;
                }
            }
            seen.push(val);
        }
    });

    return !isDuplicate;
}, "Duplicate phone numbers are not allowed");

$(function(){
    $.ajaxSetup({ 
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } 
    });

    var table = $('#contactsTable').DataTable({
        processing: true,
        serverSide: true,
        order:[],
        ajax: "{{ route('contacts.index') }}",
        columns: [
            { data: 'name' },
            { data: 'emails', orderable: false, searchable: true },
            { data: 'phones', orderable: false, searchable: true },
            { data: 'gender',className:'dt-center' },
            { data: 'custom_fields' },
            { data: 'action',className:'dt-center', orderable: false, searchable: false }
        ]
    });

    function loadCustomFields(values = {}) 
    {
        $.get("{{ route('contacts.customFields') }}", function(fields) {
            let html = '';
            fields.forEach(f => {
                const value = values[f.id] || '';
                html += `<div class='mb-2'>
                    <label>${f.name}</label>
                    <input type="${f.field_type}" class="form-control" autocomplete='off' name="custom_fields[${f.id}]" value="${value}" ${f.is_required ? 'required' : ''}>
                </div>`;
            });
            $('#custom-fields-section').html(html);
        });
    }

    $('#btn-add').on('click', () => {
        $('#contact-form')[0].reset();
        $('#contact-id').val('');
        $('.modal-title').text('Add Contact');
        $('#contact-submit-btn').text('Save');
        jQuery('#email-fields').html('');
        jQuery('#phone-fields').html('');
        $('#profile-image-preview').html('')
        $('#additional-file-preview').html('');

        loadCustomFields();
        var validator = $("#contact-form").validate();

        validator.resetForm();
        jQuery('#contact_form_error').addClass('d-none');
        jQuery('#contact_form_error').html('');
        $('#contactModal').modal('show');
    });

    $(document).on('click', '.btn-edit', function(){
        const id = $(this).data('id');

        $.ajax({
            url: "{{ route('contacts.edit') }}",
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
                    const d = res.msg;
                    $('#contact-id').val(d.id);
                    $('#contact-name').val(d.name);
                    
                    $(`input[name='gender'][value='${d.gender}']`).prop('checked', true);
                    if(d.custom_fields)
                    {
                        loadCustomFields(d.custom_fields);
                    }
                    
                    if(d.profile_image_url)
                    {
                        $('#profile-image-preview').html('<a href="'+d.profile_image_url+'" target="_blank">View Profile Image</a>');
                    } 
                    else 
                    {
                        $('#profile-image-preview').html('');
                    }

                    if (d.additional_file_url) {
                        $('#additional-file-preview').html('<a href="'+d.additional_file_url+'" target="_blank">View Additional File</a>');
                    } else {
                        $('#additional-file-preview').html('');
                    }

                    // Clear existing fields
                    $('#email-fields').empty();
                    $('#phone-fields').empty();

                    // Handle emails
                    if (d.emails && d.emails.length > 0) {
                        d.emails.forEach((item, index) => {
                            if (index === 0) {
                                // First field (main one already exists)
                                $('.contact_email').val(item.email);
                            } else {
                                // Add additional fields dynamically
                                $('#email-fields').append(`
                                    <div class="mb-2 row">
                                        <div class="col-md-8">
                                            <input type='text' name="email[]" class="form-control contact_email" autocomplete="off" value="${item.email}" placeholder="Email">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove_email">-</button>
                                        </div>
                                    </div>
                                `);
                            }
                        });
                    } else {
                        $('.contact_email').val('');
                    }

                    // Handle phones
                    if (d.phones && d.phones.length > 0) {
                        d.phones.forEach((item, index) => {
                            if (index === 0) {
                                $('.contact_phone').val(item.phone);
                            } else {
                                $('#phone-fields').append(`
                                    <div class="mb-2 row">
                                        <div class="col-md-8">
                                            <input type='text' name="phone[]" class="form-control contact_phone" autocomplete="off" value="${item.phone}" placeholder="Phone">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove_phone">-</button>
                                        </div>
                                    </div>
                                `);
                            }
                        });
                    } else {
                        $('.contact_phone').val('');
                    }

                    $('.modal-title').text('Edit Contact');
                    $('#contact-submit-btn').text('Update');
                    $('#contactModal').modal('show');
                } else {
                    toastr.error('Failed to fetch contact');
                }
            },
            error: function (xhr) {
                $("body").LoadingOverlay("hide");
                toastr.error('Failed to fetch contact');
            }
        });
    });

    $('#contact-form').validate({
        rules: {
            name: {
                required: true,
                maxlength:25
            },
            'email[]':{
                email:true,
                uniqueEmail: true
            },
            'phone[]':{
                required: true,
                number: true,
                uniquePhone: true,
                minlength:10,
                maxlength:10
            },
            profile_image:{
                extension: "jpg|jpeg|png",
                filesize: 2097152
            },
            additional_file:{
                extension: "jpg|jpeg|png|pdf",
                filesize: 2097152
            }
        },
        submitHandler: function(form) {
            
            let id = $('#contact-id').val();
            let formData = new FormData(form);
            if (id) formData.append('_method', 'PUT');

            $.ajax({
                url: id ? "{{ route('contacts.update', ':id') }}".replace(':id', id) : "{{ route('contacts.store') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend:function(){
                    $("body").LoadingOverlay("show");
                },
                success(res)
                {
                    $("body").LoadingOverlay("hide");
                    if (res.success) 
                    {
                        jQuery('#contact_form_error').addClass('d-none');
                        jQuery('#contact_form_error').html('');
                        $('#contactModal').modal('hide');
                        table.ajax.reload(null, false);
                        
                        toastr.success(res.msg);
                    } 
                    else 
                    {
                        jQuery('#contact_form_error').removeClass('d-none');
                        jQuery('#contact_form_error').html(res.msg);
                    }
                },
                error:function()
                {
                    $("body").LoadingOverlay("hide");
                    jQuery('#contact_form_error').removeClass('d-none');
                    jQuery('#contact_form_error').html('Something went wrong!');
                }
            });
        }
    });

    jQuery(document).on('click', '.add_email_fields', function(e) {
        let c = '<div class="row mb-2">';
            c += '<div class="col-md-8">';
            c +='<input type="text" name="email[]" class="form-control contact_email" placeholder="Email" autocomplete="off" autocomplete="off">';
            c +='</div>';
            c += '<div class="col-md-2">';
            c += '<button type="button" class="btn btn-danger ms-2 remove_phone">-</button>';
            c +='</div>';
            c +='</div>';
        
        jQuery('#email-fields').append(c);

        let d=0;
        jQuery('.contact_email').each(function() {
        
            jQuery(this).attr('name', 'email['+d+']');
            
            jQuery(this).rules('add', {email:true,uniqueEmail: true});
            d++;
        });
    });

    jQuery(document).on('click', '.remove_email', function(e) {
        jQuery(this).parent().parent().remove();
        let d=0;
        jQuery('.contact_email').each(function() {
            jQuery(this).attr('name', 'email['+d+']');

            d++;
        });
    });

    jQuery(document).on('click', '.add_phone_fields', function(e) {
        let c = '<div class="row mb-2">';
            c += '<div class="col-md-8">';
            c +='<input type="text" name="phone[]" class="form-control contact_phone" placeholder="Phone" required autocomplete="off">';
            c +='</div>';
            c += '<div class="col-md-2">';
            c += '<button type="button" class="btn btn-danger ms-2 remove_phone">-</button>';
            c +='</div>';
            c +='</div>';
        
        jQuery('#phone-fields').append(c);

        let d=0;
        jQuery('.contact_phone').each(function() {
            jQuery(this).attr('name', 'phone['+d+']');
            jQuery(this).rules('add', {required: true, number: true,uniquePhone:true, minlength:10, maxlength:10});
            d++;
        });
    });

    jQuery(document).on('click', '.remove_phone', function(e) {
        jQuery(this).parent().parent().remove();
         let d=0;
        jQuery('.contact_phone').each(function() {
            jQuery(this).attr('name', 'phone['+d+']');
            jQuery(this).rules('add', {required: true, number: true,uniquePhone:true, minlength:10, maxlength:10});
            d++;
        });
    });

    $(document).on('click', '.btn-delete', function () {
        if (!confirm('Are you sure you want to delete this contact?')) return;

        const id = $(this).data('id');

        $.ajax({
            url: "{{ route('contacts.destroy') }}",
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
                    toastr.error('Contact not deleted. Something went wrong!');
                }
            },
            error: function () {
                $("body").LoadingOverlay("hide");
                toastr.error('Something went wrong! An error occurred while deleting the Contact.');
            }
        });
    });

    $('#btn-merge-contact').on('click', () => {
        $('#merge-contact-form')[0].reset();

        var validator = $("#merge-contact-form").validate();

        validator.resetForm();

        jQuery('#merge_contact_error').addClass('d-none');
        jQuery('#merge_contact_error').html('');

        $.ajax({
            url: "{{ route('contacts.active') }}",
            type: 'GET',
            dataType: 'json',
            async:false,
            success: function (data) {
                let masterSelect = $('#master_contact');
                let secondarySelect = $('#secondary_contact');
                
                masterSelect.empty();
                secondarySelect.empty();

                masterSelect.append('<option value="">Select Master Contact</option>');
                secondarySelect.append('<option value="">Select Secondary Contact</option>');

                $.each(data, function (index, contact) {
                    masterSelect.append('<option value="' + contact.id + '">' + contact.name + '</option>');
                    secondarySelect.append('<option value="' + contact.id + '">' + contact.name + '</option>');
                });
            },
            error: function (xhr, status, error) {
                console.error('Error loading contacts:', error);
            }
        });
    
        $('#merge_contact_modal').modal('show');
    });

    $('#merge-contact-form').validate({
        rules: {
            master_contact_id: {
                required: true
            },
            secondary_contact_id:{
                required: true,
                notEqualTo: "#master_contact"
            }
        },
        messages: {
            master_contact_id: {
                required: "Please select a master contact."
            },
            secondary_contact_id: {
                required: "Please select a secondary contact.",
                notEqualTo: "Master and Secondary contacts cannot be the same."
            }
        },
        submitHandler: function(form) {

            if (confirm('Are you sure you want to merge these contacts? This action cannot be undone.')) {
                $.ajax({
                    url: "{{ route('contacts.merge_contacts') }}",
                    type: 'POST',
                    data: $('#merge-contact-form').serialize(),
                    dataType: 'json',
                    beforeSend:function(){
                        $("body").LoadingOverlay("show");
                    },
                    success:function(res)
                    {
                        $("body").LoadingOverlay("hide");
                        if (res.success) 
                        {
                            jQuery('#merge_contact_error').addClass('d-none');
                            jQuery('#merge_contact_error').html('');
                            $('#merge_contact_modal').modal('hide');
                            table.ajax.reload(null, false);
                            
                            toastr.success(res.msg);
                        } 
                        else 
                        {
                            jQuery('#merge_contact_error').removeClass('d-none');
                            jQuery('#merge_contact_error').html(res.msg);
                        }
                    },
                    error:function()
                    {
                        $("body").LoadingOverlay("hide");
                        jQuery('#merge_contact_error').removeClass('d-none');
                        jQuery('#merge_contact_error').html('Something went wrong!');
                    }
                });
            }
        }
    });
});
</script>