<script>
$(document).ready(function() {
        $('form#ajaxform').submit(function() {
            $.ajax({
                type: 'post',
                cache: false,
                dataType: 'json',
                data: $('form#ajaxform').serialize(),
                beforeSend: function() { 
                    $("#validation-errors").hide().empty(); 
                },
                success: function(data) {
                    if(data.success == false)
                    {
                        var arr = data.errors;
                        $.each(arr, function(index, value)
                        {
                            if (value.length != 0)
                            {
                                $("#validation-errors").append('<div class="alert alert-error"><strong>'+ value +'</strong><div>');
                            }
                        });
                        $("#validation-errors").show();
                    } else {
                         location.reload();
                    }
                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went to wrong.Please Try again later...');
                }
            });
            return false;
    });
});
</script>
<div class="page-header">
    <h3>Sign in into your account</h3>
</div>
<div class="row">
    <form method="post" action="{{ route('signin') }}" id="ajaxform" class="form-horizontal">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
 
            <div id="validation-errors" style="display: none"></div>
 
        <!-- Email -->
        <div class="control-group{{ $errors->first('email', ' error') }}">
            <label class="control-label" for="email">Email</label>
            <div class="controls">
                <input type="text" name="email" id="email" value="{{ Input::old('email') }}" />
                {{ $errors->first('email', '<span class="help-block">:message</span>') }}
            </div>
        </div>
 
        <!-- Password -->
        <div class="control-group{{ $errors->first('password', ' error') }}">
            <label class="control-label" for="password">Password</label>
            <div class="controls">
                <input type="password" name="password" id="password" value="" />
                {{ $errors->first('password', '<span class="help-block">:message</span>') }}
            </div>
        </div>
 
        <!-- Remember me -->
        <div class="control-group">
            <div class="controls">
            <label class="checkbox">
                <input type="checkbox" name="remember-me" id="remember-me" value="1" /> Remember me
            </label>
            </div>
        </div>
 
        <hr>
 
        <!-- Form actions -->
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn">Sign in</button>                
            </div>
        </div>
    </form>
</div>
