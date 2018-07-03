<div id="footer--upper">
    <div class="container">
        <div id="footer--upper--about">
            <h1>Contact Us</h1>
            <div>
                <span class="fa fa-envelope"></span>
                <a href="mailto:info@bts-crew.com">info@bts-crew.com</a>
            </div>
            <div>
                <span class="fa fa-phone"></span>
                +44 (0) 1225 383067
            </div>
            <div>
                <span class="fa fa-home"></span>
                1E 3.4
            </div>
            <div>
                <span class="fa fa-map-marker"></span>
                The SU, University of Bath, Bath, BA2 7AY
            </div>
        </div>
        <div id="footer--upper--links">
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
</div>
<div id="footer--lower">
    <div class="container">
        <div id="footer--lower--social">
            <a href="https://www.facebook.com/BackstageTechnicalServices" target="_blank">
                <span class="fa fa-facebook" title="Follow us on facebook"></span>
            </a>
            <a href="https://www.twitter.com/backstagetech" target="_blank">
                <span class="fa fa-twitter" title="Follow us on twitter"></span>
            </a>
            <a href="https://plus.google.com/104029009513703982895" target="_blank">
                <span class="fa fa-google-plus" title="Follow us on G+"></span>
            </a>
            <a href="https://www.github.com/backstagetechnicalservices/website/issues" target="_blank">
                <span class="fa fa-github" title="Report an issue"></span>
            </a>
        </div>
        <div id="footer--lower--copyright">
            <p>&copy; Backstage Technical Services {{ date('Y') }}. All rights reserved.</p>
            <p>Generously supported by the University of Bath Alumni Fund.</p>
        </div>
    </div>
</div>