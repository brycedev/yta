<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AudiusCast</title>
    <script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/home.js'])
</head>

<body class="antialiased">
    <div class="bg-white">
        <header class="absolute inset-x-0 top-0 z-50">
            <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <a href="#" class="-m-1.5 p-1.5 flex items-center">
                        <img src="/images/logo_color_small.png"
                            class="w-64">
                    </a>
                </div>
                <div class="flex flex-1 justify-end">
                    <div id="audius-login-button"></div>
                </div>
            </nav>
        </header>

        <div class="relative isolate pt-14">
            <svg class="absolute inset-0 -z-10 h-full w-full stroke-gray-200 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
                aria-hidden="true">
                <defs>
                    <pattern id="83fd4e5a-9d52-42fc-97b6-718e5d7ee527" width="200" height="200" x="50%"
                        y="-1" patternUnits="userSpaceOnUse">
                        <path d="M100 200V.5M.5 .5H200" fill="none"></path>
                    </pattern>
                </defs>
                <svg x="50%" y="-1" class="overflow-visible fill-gray-50">
                    <path
                        d="M-100.5 0h201v201h-201Z M699.5 0h201v201h-201Z M499.5 400h201v201h-201Z M-300.5 600h201v201h-201Z"
                        stroke-width="0"></path>
                </svg>
                <rect width="100%" height="100%" stroke-width="0" fill="url(#83fd4e5a-9d52-42fc-97b6-718e5d7ee527)">
                </rect>
            </svg>
            <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:flex lg:items-center lg:gap-x-10 lg:px-8 lg:py-40">
                <div class="mx-auto max-w-2xl lg:mx-0 lg:flex-auto">
                    <h1 class="mt-10 max-w-xl text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">Keep your Audius account in sync with your <span class="text-red-600">YouTube</span> channel</h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">Easily import your existing videos
                        keep your new
                        ones in sync, with one easy step. It's as simple as adding your
                        channel and watching the tracks upload automatically.</p>
                    <div class="mt-10 flex items-center gap-x-6">
                        <div onclick="login()"
                            class="cursor-pointer rounded-md bg-audius-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-audius-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-audius-600">Get
                            started</div>
                    </div>
                </div>
                <div class="mt-16 sm:mt-24 lg:mt-0 lg:flex-shrink-0 lg:flex-grow">
                    <svg viewBox="0 0 366 729" role="img" class="mx-auto w-[22.875rem] max-w-full drop-shadow-xl">
                        <title>App screenshot</title>
                        <defs>
                            <clipPath id="2ade4387-9c63-4fc4-b754-10e687a0d332">
                                <rect width="316" height="684" rx="36"></rect>
                            </clipPath>
                        </defs>
                        <path fill="#4B5563"
                            d="M363.315 64.213C363.315 22.99 341.312 1 300.092 1H66.751C25.53 1 3.528 22.99 3.528 64.213v44.68l-.857.143A2 2 0 0 0 1 111.009v24.611a2 2 0 0 0 1.671 1.973l.95.158a2.26 2.26 0 0 1-.093.236v26.173c.212.1.398.296.541.643l-1.398.233A2 2 0 0 0 1 167.009v47.611a2 2 0 0 0 1.671 1.973l1.368.228c-.139.319-.314.533-.511.653v16.637c.221.104.414.313.56.689l-1.417.236A2 2 0 0 0 1 237.009v47.611a2 2 0 0 0 1.671 1.973l1.347.225c-.135.294-.302.493-.49.607v377.681c0 41.213 22 63.208 63.223 63.208h95.074c.947-.504 2.717-.843 4.745-.843l.141.001h.194l.086-.001 33.704.005c1.849.043 3.442.37 4.323.838h95.074c41.222 0 63.223-21.999 63.223-63.212v-394.63c-.259-.275-.48-.796-.63-1.47l-.011-.133 1.655-.276A2 2 0 0 0 366 266.62v-77.611a2 2 0 0 0-1.671-1.973l-1.712-.285c.148-.839.396-1.491.698-1.811V64.213Z">
                        </path>
                        <path fill="#343E4E"
                            d="M16 59c0-23.748 19.252-43 43-43h246c23.748 0 43 19.252 43 43v615c0 23.196-18.804 42-42 42H58c-23.196 0-42-18.804-42-42V59Z">
                        </path>
                        <foreignObject width="316" height="684" transform="translate(24 24)"
                            clip-path="url(#2ade4387-9c63-4fc4-b754-10e687a0d332)">
                            <img src="/mobile.png" alt="">
                        </foreignObject>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function login() {
            document.getElementById("audius-login-button").click()
        }
    </script>
</body>

</html>
