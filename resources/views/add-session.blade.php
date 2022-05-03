@extends('layouts.main')

@section('content')

    <section class="section-1-styling">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center text-white">
                    <p class="main-heading">Coin <b style="color: #91c7b1;">Exchange</b></p>
                    <p class="second-heading Poppins-text">Lets start your transaction, Your Session ID is: &nbsp; <u>{{ $batch->session_id }}</u> </p>
                    <p class="third-heading Poppins-text">You can always use your session ID to check the status of your transaction</p>
                </div>
            </div>
        </div>
    </section>
    <section class="section-2-styling">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-3 col-lg-3 col-md-3 col-xl-3">
                    <p class="text-white Poppins-text">Send Your Coins Here:</p>
                </div>
                <div class="col-12 col-sm-9 col-lg-9 col-md-9 col-xl-9 pl-0">
                <form>
                    <input type="text" class="form-control form-width" value="{{ $batch->address }}" readonly="readonly">
                </form>
                </div>
            </div>
            <div class="repeat_field">
                
                @foreach ($batch->intermediateAddrersses as $address)
                                    
                    <div class="row pt-3 pb-2 add-row">
                        <div class="col-12 col-sm-3 col-lg-3 col-md-3 col-xl-3">
                            <p class="text-white Poppins-text">Intermediate Address:</p>
                        </div>
                        <div class="col-12 col-sm-9 col-lg-9 col-md-9 col-xl-9 pl-0">
                            <input  id="field" type="text" class="address form-control form-width" value="{{ $address->address ?? '' }}" readonly="readonly">
                        </div>
                    </div>				
                @endforeach

            </div>
            <div class="row">
                <div class="col-12 col-sm-4 col-md-4 col-lg-4 col-xl-4"></div>
                <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 d-flex justify-content-end">
                    <button id="b1" class="btn add-more button-styling Poppins-text " type="button">Add Fields</button>
                </div>
            </div>
            <div class="row py-3">
                <div class="col-12 col-sm-3 col-lg-3 col-md-3 col-xl-3">
                    <p class="text-white Poppins-text">Final Receiver Address:</p>
                </div>
                <div class="col-12 col-sm-9 col-lg-9 col-md-9 col-xl-9 pl-0">
                <form>
                    <input type="text" class="form-control form-width final_address">
                </form>
                </div>
            </div>
        </div>

        <div class="row pt-3 pb-2 add-row text-center">
            <div class="col-12 col-sm-9 col-lg-9 col-md-9 col-xl-9 pl-0">
                <input type="button" class="add-batch btn button-styling Poppins-text" value="Submit" />
            </div>
        </div>				

    </section>
    <section class="section-3-styling">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <p class="text-center recieve-status-styling text-white Poppins-text"><span class="span-styling"><span class="receive-styling">RECEIVE</span> <b class="receive-styling">STATUS</b></span></p>
                </div>
                <!-- <hr> -->	
            </div>
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="progressive-bar pb-3 pl-5 ">
                        <h4 class="Poppins-text active">Done</h4>
                        <p class="text-white Poppins-text font-size">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br> Ut enim ad minim veniam, quis nostrud exercitation ullamco</p>
                        <span class="right_icon"><i class="fas fa-check"></i></span>
                    </div>
                    <div class="progressive-bar pb-3 pl-5">
                        <h4 class="Poppins-text active">Pending</h4>
                        <p class="text-white Poppins-text font-size">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br> Ut enim ad minim veniam, quis nostrud exercitation ullamco</p>
                        <span class="progress_circle"></span>
                    </div>
                    <div class="progressive-bar pb-3 pl-5">
                        <h4 class="Poppins-text">Validation 1</h4>
                        <p class="text-white Poppins-text font-size">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br> Ut enim ad minim veniam, quis nostrud exercitation ullamco</p>
                    </div>
                    <div class="progressive-bar hide_line pb-3 pl-5">
                        <h4 class="Poppins-text">Validation 2</h4>
                        <p class="text-white Poppins-text font-size">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.<br> Ut enim ad minim veniam, quis nostrud exercitation ullamco</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-4-styling">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-5">
                    <div class="bitcoin-img">
                        <img class="img-fluid " src="{{ asset('assets/images/1.jpg') }}">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
@endsection

@push('script')

    <script>
        $(document).ready(function(){
            $(".add-more").click(function(){

                $.ajax({
                    url: `{{ route('intermediate-addess.store') }}`,
                    method: 'POST',
                    data : { 
                        _token: "{{ csrf_token() }}" 
                    },
                    success: function(response) {
                        let row = $( ".add-row" ).clone();
                        $(row).find('.address').val(response.data.address)
                        row.appendTo(".repeat_field").removeClass('add-row');

                        if(response.data.count)
                            $('#b1').attr('disabled', true);  
                    },
                    error: function(response) {
                        console.log(response);
                    }
                })
                
            });

            $('.add-batch').click(function() {

                if(!confirm('Have you copied the address ?'))
                    return false;

                let final_address = $('.final_address').val();
                
                $.ajax({
                    url: `{{ route('session.process-batch') }}`,
                    method: 'POST',
                    data : { 
                        _token: "{{ csrf_token() }}",
                        final_address: final_address
                    },
                    success: async function(response) {
                        // console.log(response)
                        if(!response.status) {
                            console.log(response.data.error)
                            toastr.error(response.data.message)
                            return;
                        }
                        toastr.success(response.data.message + ' , Redirecting ...');
                        await new Promise(r => setTimeout(r, 5000));
                        window.location.replace('/');
                    }
                })
            })
        }); 
    </script>
    
@endpush