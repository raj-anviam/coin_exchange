@extends('layouts.main')

@section('content')

    <div class="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-sm-2 col-md-2 col-lg-2 col-xl-2 d-flex align-items-center">
                    <div class="image">
                        <img class="img-fluid" src="{{ asset('assets/images/Advertisement.png') }}">
                    </div>
                </div>
                <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8">
                    <div class="logo">
                        <p>Coin <span class="exchange-class"><b> Exchange</b> </span></p>

                        <div class="text-center">
                            <input type="text" class="form-control search" placehoder="Search Session Id">
                            <button class="add-batch btn button-styling Poppins-text mt-4 search-btn">Search</button>
                        </div>
                    </div>
                    <div class="content-container">
                        <h3 class="we-are-main-styling">We are the awesome coin exchange from iceland!</h3>
                        <h2>We buy, sell exchange bitcoins.</h2>
                    </div>
                    <div class="start_btn my-5">
                        <a href="{{ route('session.create') }}">START</a>
                        <span></span>
                    </div>
                    <div class="points mt-4">
                        <ul>
                            <li><img src="{{ asset('assets/images/right-arrow.png') }}"><p class="mb-1">Exchange Rate: 0.01%</p></li>
                            <li><img src="{{ asset('assets/images/right-arrow.png') }}"></i><p class="mb-1">Current Bitcoin Price</p></li>
                            <li><img src="{{ asset('assets/images/right-arrow.png') }}"></i><p class="mb-1">Total Number of Exchanges</p></li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-2 col-md-2 col-lg-2 col-xl-2 d-flex align-items-center">
                    <div class="image">
                        <img class="img-fluid" src="{{ asset('assets/images/Advertisement.png') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('script')
        <script>
            $('.search-btn').click(function() {

                let search = $('.search').val();
                
                $.ajax({
                    url: `{{ url('/search') }}/${search}`,
                    success: function(response) {
                        if(!response.status)
                            toastr.error(response.data.message)
                        else
                            toastr.info(response.data.message)
                    }
                })
            })
        </script>
    @endpush
    
@endsection