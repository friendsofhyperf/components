<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="/vendor/telescope/favicon.ico">
    <title>Hyperf Telescope</title>

    <!-- Style sheets-->
    <link href="/vendor/telescope/app.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="telescope" v-cloak>
    <alert :message="alert.message"
           :type="alert.type"
           :auto-close="alert.autoClose"
           :confirmation-proceed="alert.confirmationProceed"
           :confirmation-cancel="alert.confirmationCancel"
           v-if="alert.type"></alert>

    <div class="container mb-5">
        <div class="d-flex align-items-center py-4 header">
            <router-link to="/" class="logo d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
                    <path class="fill-primary" d="M0 40a39.87 39.87 0 0 1 11.72-28.28A40 40 0 1 1 0 40zm34 10a4 4 0 0 1-4-4v-2a2 2 0 1 0-4 0v2a4 4 0 0 1-4 4h-2a2 2 0 1 0 0 4h2a4 4 0 0 1 4 4v2a2 2 0 1 0 4 0v-2a4 4 0 0 1 4-4h2a2 2 0 1 0 0-4h-2zm24-24a6 6 0 0 1-6-6v-3a3 3 0 0 0-6 0v3a6 6 0 0 1-6 6h-3a3 3 0 0 0 0 6h3a6 6 0 0 1 6 6v3a3 3 0 0 0 6 0v-3a6 6 0 0 1 6-6h3a3 3 0 0 0 0-6h-3zm-4 36a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM21 28a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                </svg>

                <h4 class="mb-0 ml-3"><strong>Hyperf Telescope</strong></h4>
            </router-link>

            <button class="btn btn-muted ml-auto mr-3 d-flex align-items-center py-2" v-on:click.prevent="toggleRecording" :title="recording ? 'Pause recording' : 'Resume recording'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon" fill="currentColor" v-if="recording">
                    <path d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon" fill="currentColor" v-else>
                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                </svg>
            </button>

            <button class="btn btn-muted mr-3 d-flex align-items-center py-2" v-on:click.prevent="clearEntries" title="Clear entries">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                </svg>
            </button>

            <button class="btn btn-muted mr-3 d-flex align-items-center py-2" :class="{active: autoLoadsNewEntries}" v-on:click.prevent="autoLoadNewEntries" title="Auto load entries">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div class="row mt-4">
            <div class="col-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <router-link active-class="active" to="/requests" class="nav-link d-flex align-items-center pt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M0 3c0-1.1.9-2 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3zm2 2v12h16V5H2zm8 3l4 5H6l4-5z"></path>
                            </svg>
                            <span>Requests</span>
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link active-class="active" to="/services" class="nav-link d-flex align-items-center pt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M7 17H2a2 2 0 0 1-2-2V2C0 .9.9 0 2 0h16a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2h-5l4 2v1H3v-1l4-2zM2 2v11h16V2H2z"></path>
                            </svg>
                            <span>Services</span>
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link active-class="active" to="/exceptions" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M15.3 14.89l2.77 2.77a1 1 0 0 1 0 1.41 1 1 0 0 1-1.41 0l-2.59-2.58A5.99 5.99 0 0 1 11 18V9.04a1 1 0 0 0-2 0V18a5.98 5.98 0 0 1-3.07-1.51l-2.59 2.58a1 1 0 0 1-1.41 0 1 1 0 0 1 0-1.41l2.77-2.77A5.95 5.95 0 0 1 4.07 13H1a1 1 0 1 1 0-2h3V8.41L.93 5.34a1 1 0 0 1 0-1.41 1 1 0 0 1 1.41 0l2.1 2.1h11.12l2.1-2.1a1 1 0 0 1 1.41 0 1 1 0 0 1 0 1.41L16 8.41V11h3a1 1 0 1 1 0 2h-3.07c-.1.67-.32 1.31-.63 1.89zM15 5H5a5 5 0 1 1 10 0z"></path>
                            </svg>
                            <span>Exceptions</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/queries" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1c3.866 0 7 1.79 7 4s-3.134 4-7 4-7-1.79-7-4 3.134-4 7-4zm5.694 8.13c.464-.264.91-.583 1.306-.952V10c0 2.21-3.134 4-7 4s-7-1.79-7-4V8.178c.396.37.842.688 1.306.953C5.838 10.006 7.854 10.5 10 10.5s4.162-.494 5.694-1.37zM3 13.179V15c0 2.21 3.134 4 7 4s7-1.79 7-4v-1.822c-.396.37-.842.688-1.306.953-1.532.875-3.548 1.369-5.694 1.369s-4.162-.494-5.694-1.37A7.009 7.009 0 013 13.179z" clip-rule="evenodd"></path></svg>
                            <span>Queries</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/logs" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2 3a1 1 0 00-1 1v1a1 1 0 001 1h16a1 1 0 001-1V4a1 1 0 00-1-1H2z"></path> <path fill-rule="evenodd" d="M2 7.5h16l-.811 7.71a2 2 0 01-1.99 1.79H4.802a2 2 0 01-1.99-1.79L2 7.5zM7 11a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                            <span>Logs</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/redis" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M.96 9.84c1.35.61 6.83 2.83 7.73 3.26.93.44 1.58.45 2.75-.16 1.13-.59 6.21-2.68 7.6-3.36l.16.06c1.04.38 1.08.7.01 1.25-1.06.56-6.59 2.83-7.77 3.44-1.17.62-1.82.61-2.75.17-.93-.45-6.81-2.82-7.87-3.33-1.06-.5-1.08-.85-.04-1.26l.18-.07zM.8 13.19h.01c1.06.5 6.94 2.88 7.87 3.33.93.44 1.58.45 2.75-.17 1.17-.6 6.6-2.84 7.74-3.42h.02c1.04.39 1.08.7.01 1.26-1.06.55-6.59 2.82-7.77 3.44-1.17.61-1.82.6-2.75.16-.93-.44-6.81-2.82-7.87-3.33-1.06-.5-1.08-.85-.04-1.26l.03-.01zm18.4-5.71c-1.06.55-6.59 2.82-7.77 3.44-1.17.61-1.82.6-2.75.16-.93-.44-6.81-2.82-7.87-3.32C-.24 7.25-.26 6.9.78 6.49c1.04-.4 6.89-2.7 8.12-3.14 1.24-.44 1.67-.46 2.72-.07 1.05.38 6.54 2.57 7.58 2.95 1.04.38 1.08.7.01 1.25zm-6.59-1.95l-1.34-.5.36-.86-1.32.44-1.4-.55.45.83-1.5.53 2 .18.63 1.04.39-.93 1.73-.18zm-2.22 4.53L11.8 8l-4.63.7 3.23 1.35zm-4.48-2.1c1.37 0 2.47-.42 2.47-.95s-1.1-.96-2.47-.96-2.48.43-2.48.96 1.11.96 2.48.96zm8.75-2.17v2.16l2.74-1.08-2.74-1.08zm-3.03 1.2l2.73 1.08.3-.12V5.8l-3.03 1.2z"></path></svg>
                            <span>Redis</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/events" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.92 3.845a19.361 19.361 0 01-6.3 1.98C6.765 5.942 5.89 6 5 6a4 4 0 00-.504 7.969 15.974 15.974 0 001.271 3.341c.397.77 1.342 1 2.05.59l.867-.5c.726-.42.94-1.321.588-2.021-.166-.33-.315-.666-.448-1.004 1.8.358 3.511.964 5.096 1.78A17.964 17.964 0 0015 10c0-2.161-.381-4.234-1.08-6.155zM15.243 3.097A19.456 19.456 0 0116.5 10c0 2.431-.445 4.758-1.257 6.904l-.03.077a.75.75 0 001.401.537 20.902 20.902 0 001.312-5.745 1.999 1.999 0 000-3.545 20.902 20.902 0 00-1.312-5.745.75.75 0 00-1.4.537l.029.077z"></path></svg>
                            <span>Events</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/cache" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 01-.134 1.051 2.494 2.494 0 00-.93 2.437 2.494 2.494 0 002.437-.93.75.75 0 111.186.918 3.995 3.995 0 01-4.482 1.332.75.75 0 01-.461-.461 3.994 3.994 0 011.332-4.482.75.75 0 011.052.134z" clip-rule="evenodd"></path> <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 008 14.248v4.002c0 .414.336.75.75.75a5 5 0 004.797-6.414 12.984 12.984 0 005.45-10.848.75.75 0 00-.735-.735 12.984 12.984 0 00-10.849 5.45A5 5 0 001 11.25c.001.414.337.75.751.75h4.002zM13 9a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                            <span>Caches</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/commands" class="nav-link d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.25 3A2.25 2.25 0 001 5.25v9.5A2.25 2.25 0 003.25 17h13.5A2.25 2.25 0 0019 14.75v-9.5A2.25 2.25 0 0016.75 3H3.25zm.943 8.752a.75.75 0 01.055-1.06L6.128 9l-1.88-1.693a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 01-1.06-.055zM9.75 10.25a.75.75 0 000 1.5h2.5a.75.75 0 000-1.5h-2.5z" clip-rule="evenodd"></path></svg>
                            <span>Commands</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link active-class="active" to="/client-request" class="nav-link d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-1.5 0a6.5 6.5 0 11-11-4.69v.447a3.5 3.5 0 001.025 2.475L8.293 10 8 10.293a1 1 0 000 1.414l1.06 1.06a1.5 1.5 0 01.44 1.061v.363a1 1 0 00.553.894l.276.139a1 1 0 001.342-.448l1.454-2.908a1.5 1.5 0 00-.281-1.731l-.772-.772a1 1 0 00-1.023-.242l-.384.128a.5.5 0 01-.606-.25l-.296-.592a.481.481 0 01.646-.646l.262.131a1 1 0 00.447.106h.188a1 1 0 00.949-1.316l-.068-.204a.5.5 0 01.149-.538l1.44-1.234A6.492 6.492 0 0116.5 10z" clip-rule="evenodd"></path></svg>
                            <span>Client Request</span>
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="col-10">
                <router-view></router-view>
            </div>
        </div>
    </div>
</div>

<!-- Global Telescope Object -->
<script>
    window.Telescope = $telescopeScriptVariables;
</script>

<script src="/vendor/telescope/app.js"></script>
</body>
</html>
