<div class="container">
    <!-- Left footer -->
    <div class="col-sm-4">
        <h1>Contact Us</h1>
        <div class="row">
            <span class="fa fa-envelope pull-left"></span>
            <span class="pull-left"><a href="mailto:info@bts-crew.com">info@bts-crew.com</a></span>
        </div>
        <div class="row">
            <span class="fa fa-phone pull-left"></span>
            <span class="pull-left">+44 (0) 1225 383067</span>
        </div>
        <div class="row">
            <span class="fa fa-home pull-left"></span>
            <span class="pull-left">1E 3.4</span>
        </div>
        <div class="row">
            <span class="fa fa-map-marker pull-left"></span>
            <div class="pull-left">
                The SU<br>
                University of Bath<br>
                Claverton Down<br>
                Bath<br>
                BA2 7AY
            </div>
        </div>
    </div>
    <!-- Centre footer -->
    <div class="col-sm-5">
        <h1>Who are we?</h1>
        <p>We are a student-run society at the University of Bath that provides technical expertise to other Students' Union clubs and societies.</p>
        <p>We support every kind of event - from small band nights in the Tub to the experiences that are Freshers' Week and Summer Ball.</p>
    </div>
    <!-- Right footer -->
    <div class="col-sm-3">
        <h1>Quick Links</h1>
        <ul>
            <li><a href="{{ route('contact.enquiries') }}">Contact Us</a></li>
            <li><a href="{{ route('contact.book') }}">Book Us</a></li>
            <li><a href="{{ route('contact.book.terms') }}">Terms and Conditions</a></li>
            <li><a href="{{ route('page.show', ['slug' => 'faq']) }}">Frequently Asked Questions</a></li>
            <li><a href="{{ route('page.show', ['slug' => 'links']) }}">Useful Links</a></li>
            <li><a href="{{ route('page.show', ['slug' => 'privacy-policy']) }}">Privacy Policy</a></li>
        </ul>
    </div>
</div>
