<!DOCTYPE html>
<html>
<head>
	<title>coin-exchange</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  	<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
  	<link rel="stylesheet" type="text/css" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
  	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">

</head>
<body>


@yield('content')

@stack('script')
<script>
	
	toastr.options = { "closeButton" : true, "progressBar" : true }
	
	@if(Session::has('error'))
		toastr.error("{{ Session::get('error') }}")
	@endif

	@if(Session::has('success'))
		toastr.success("{{ Session::get('success') }}")
	@endif
	
</script>

</body>
</html>