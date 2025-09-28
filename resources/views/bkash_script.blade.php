<script type="text/javascript">

    function BkashPayment() {
        showLoading();
        // get token
        let request = {_token : "{{ csrf_token() }}"};
        $.ajax({
            url: "{{ route('bkash-get-token') }}",
            type: 'POST',
            data: JSON.stringify(request),
            contentType: 'application/json',
            success: function (data) {
                $('pay-with-bkash-button').trigger('click');
                if (data.hasOwnProperty('msg')) {
                    showErrorMessage(data) // unknown error
                }
            },
            error: function (err) {
                hideLoading();
                showErrorMessage(err);
            }
        });
    }
    let paymentID = '';
    bKash.init({
        paymentMode: 'checkout',
        paymentRequest: {},
        createRequest: function (request) {
            setTimeout(function () {
                createPayment(request);
            }, 2000)
        },
        executeRequestOnAuthorization: function (request) {
            $.ajax({
                url: '{{ $ex_payment_url }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    "paymentID": paymentID,
                    "_token": "{{ csrf_token() }}"
                }),
                success: function (data) {
                    if (data) {
                    // //Uncomment to generate Query Payment Report
                    // if (data.paymentID != null) {
                        if (data.paymentID != null) {
                            // console.log(data);
                            BkashSuccess(data);
                        } else {
                            showErrorMessage(data);
                            bKash.execute().onError();
                        }
                    } else {
                        $.get('{{ route('bkash-query-payment') }}', {
                            payment_info: {
                                payment_id: paymentID
                            }
                        }, function (data) {
                            if (data.transactionStatus === 'Completed') {
                                BkashSuccess(data);
                            } else {
                                showErrorMessage(data);
                                bKash.execute().onError();
                            }
                        });
                    }
                },
                error: function (err) {
                    showErrorMessage(err.responseJSON);
                    bKash.execute().onError();
                }
            });
        },
        onClose: function () {
            $('#error_msg').html('You canceled this Payment')
            $('#error').show();
        }
    });
    function createPayment(request) {
        // Amount already checked and verified by the controller
        // because of createRequest function finds amount from this request
        request['amount'] = $("#amount").html(); // max two decimal points allowed
        request['_token'] = "{{ csrf_token() }}";
        $.ajax({
            url: '{{ $create_payment_url }}',
            data: JSON.stringify(request),
            type: 'POST',
            contentType: 'application/json',
            success: function (data) {
                hideLoading();
                if (data && data.paymentID != null) {
                    paymentID = data.paymentID;
                    bKash.create().onSuccess(data);
                } else {
                    showErrorMessage(data);
                    bKash.create().onError();
                }
            },
            error: function (err) {
                hideLoading();
                showErrorMessage(err.responseJSON);
                bKash.create().onError();
            }
        });
    }
    function BkashSuccess(data) {
        $.post('{{ route('bkash-success') }}', {
            payment_info: data,
            "_token": "{{ csrf_token() }}"
        }, function (res) {
             window.location.href = "{{ $success_url }}";
        });
    }
    function showErrorMessage(response) {
        let message = 'Unknown Error';
        if (response.hasOwnProperty('errorMessage')) {
            let errorCode = parseInt(response.errorCode);
            let bkashErrorCode = [2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014,
                2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030,
                2031, 2032, 2033, 2034, 2035, 2036, 2037, 2038, 2039, 2040, 2041, 2042, 2043, 2044, 2045, 2046,
                2047, 2048, 2049, 2050, 2051, 2052, 2053, 2054, 2055, 2056, 2057, 2058, 2059, 2060, 2061, 2062,
                2063, 2064, 2065, 2066, 2067, 2068, 2069, 2070, 503,
            ];
            if (bkashErrorCode.includes(errorCode)) {
                message = response.errorMessage
            }
        }
        if(message == 'Unauthorised'){
            $.ajax({
                url: "{{ route('set.session') }}",
                type: "GET",
                success: function (data) {
                    window.location.href = "{{ $success_url }}";
                }
            });
        } else {
            $('#error').show();
            $('#error_msg').html('Payment failed! '+message);
        }
    }

    function showLoading() {
        $('#full_page_loading').removeClass('hidden');
    }

    function hideLoading() {
        $('#full_page_loading').addClass('hidden');
    }
</script>
