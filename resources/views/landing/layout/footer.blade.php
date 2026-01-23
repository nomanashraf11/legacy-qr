<footer class="footer bg-dark-footer relative text-gray-200 bg-slate-900">
    <div class="pt-[20px] px-0 border-t border-slate-800">
        <div class="container relative">
            @php
                $instagram = App\Models\User::role('admin')->first()->admin->instagram;
                $facebook = App\Models\User::role('admin')->first()->admin->facebook;
                // dd($instagram);
            @endphp
            <br>
            <div class="flex footer-text items-center flex-wrap justify-center gap-5">
                <p>©
                    <script>
                        document.write(new Date().getFullYear())
                    </script>
                    Living Legacy LLC. All Rights Reserved.
                </p>
                <div class="flex items-center gap-5">

                    <a href="{{ $facebook ?? 'https://facebook.com' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            style="fill: rgb(255, 255, 255);">
                            <path
                                d="M12.001 2.002c-5.522 0-9.999 4.477-9.999 9.999 0 4.99 3.656 9.126 8.437 9.879v-6.988h-2.54v-2.891h2.54V9.798c0-2.508 1.493-3.891 3.776-3.891 1.094 0 2.24.195 2.24.195v2.459h-1.264c-1.24 0-1.628.772-1.628 1.563v1.875h2.771l-.443 2.891h-2.328v6.988C18.344 21.129 22 16.992 22 12.001c0-5.522-4.477-9.999-9.999-9.999z">
                            </path>
                        </svg>
                    </a>
                    <a href="{{ $instagram ?? 'https://instagram.com' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            style="fill: rgba(255, 255, 255);">
                            <path
                                d="M20.947 8.305a6.53 6.53 0 0 0-.419-2.216 4.61 4.61 0 0 0-2.633-2.633 6.606 6.606 0 0 0-2.186-.42c-.962-.043-1.267-.055-3.709-.055s-2.755 0-3.71.055a6.606 6.606 0 0 0-2.185.42 4.607 4.607 0 0 0-2.633 2.633 6.554 6.554 0 0 0-.419 2.185c-.043.963-.056 1.268-.056 3.71s0 2.754.056 3.71c.015.748.156 1.486.419 2.187a4.61 4.61 0 0 0 2.634 2.632 6.584 6.584 0 0 0 2.185.45c.963.043 1.268.056 3.71.056s2.755 0 3.71-.056a6.59 6.59 0 0 0 2.186-.419 4.615 4.615 0 0 0 2.633-2.633c.263-.7.404-1.438.419-2.187.043-.962.056-1.267.056-3.71-.002-2.442-.002-2.752-.058-3.709zm-8.953 8.297c-2.554 0-4.623-2.069-4.623-4.623s2.069-4.623 4.623-4.623a4.623 4.623 0 0 1 0 9.246zm4.807-8.339a1.077 1.077 0 0 1-1.078-1.078 1.077 1.077 0 1 1 2.155 0c0 .596-.482 1.078-1.077 1.078z">
                            </path>
                            <circle cx="11.994" cy="11.979" r="3.003"></circle>
                        </svg>
                    </a>
                </div>
                <div class="flex items-center gap-5">
                    <a href="{{ route('login') }}" class="hover:underline">
                        Reseller Login
                    </a>
                    <a href="/terms-and-conditions" class="hover:underline">
                        Terms & Conditions
                    </a>
                    <a href="/privacy" class="hover:underline">
                        Privacy Policy
                    </a>
                </div>

            </div>

            <br>

        </div><!--end container-->
    </div>
</footer><!--end footer-->
<!-- Footer End -->
