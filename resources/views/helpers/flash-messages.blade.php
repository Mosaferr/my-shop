<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.9/dist/sweetalert2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>

@if (session('status'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                {{ session('status') }}
                <button class="btn-close float-end" data-dismiss="alert" aria-hidden="true">
                    {{-- &times; --}}
                </button>
            </div>
        </div>
    </div>
@endif
