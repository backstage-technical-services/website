<div class="modal-header">
    <h1>Terms and Conditions for the Provision of Services</h1>
</div>
<div class="modal-body">
    {!! Markdown::convertToHtml(view('contact.book.terms')) !!}
</div>
<div class="modal-footer">
    <div class="text-center">
        <button class="btn btn-success" id="btnAcceptTerms">
            <span class="fa fa-thumbs-up"></span>
            <span>I have read and accept these terms</span>
        </button>
    </div>
</div>
