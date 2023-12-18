<div id="footer--upper">
    <div class="container">
        <div id="footer--about">
            <div>
                <span class="fa fa-envelope"></span>
                <a href="mailto:info@bts-crew.com">info@bts-crew.com</a>
            </div>

            <div>
                <span class="fa fa-phone"></span>
                (01225) 666076
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
        <div id="footer--social">
            <a href="https://www.facebook.com/BackstageTechnicalServices" target="_blank">
                <span class="fa fa-facebook" title="Follow us on facebook"></span>
            </a>
            <a href="https://www.twitter.com/backstagetech" target="_blank">
                <span class="fa fa-twitter" title="Follow us on twitter"></span>
            </a>
            <a href="https://github.com/backstage-technical-services/hub/issues/new/choose" target="_blank">
                <span class="fa fa-github" title="Report an issue"></span>

            </a>
        </div>
    </div>
</div>
<div id="footer--lower">
    <div class="container">
        <div id="footer--links">
            <ul>
                <li><a href="{{ route('contact.enquiries') }}">Contact Us</a></li>
                <li><a href="{{ route('contact.book') }}">Book Us</a></li>
                <li><a href="{{ route('page.show', ['slug' => 'links']) }}">Useful Links</a></li>
                <li><a href="{{ route('page.show', ['slug' => 'faq']) }}">FAQ</a></li>
                <li><a href="{{ route('contact.book.terms') }}">Terms and Conditions</a></li>
                <li><a href="{{ route('page.show', ['slug' => 'privacy-policy']) }}">Privacy Policy</a></li>
            </ul>

        </div>
        <div id="footer--copyright">
            <p>&copy; Backstage Technical Services {{ date('Y') }}. All rights reserved.</p>
            <p>Generously supported by the University of Bath Alumni Fund.</p>
        </div>
    </div>
</div>
