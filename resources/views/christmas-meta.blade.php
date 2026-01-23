<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    
    <title>{{ $customTitle ? $customTitle . ' - ' . $profileName : $profileName . ' - Christmas Memories' }}</title>
    
    <meta name="description" content="🎄 Christmas Memories with {{ $profileName }} - Share and celebrate special moments this holiday season. Create lasting memories with family and friends." />
    <meta name="keywords" content="christmas memories, holiday sharing, family moments, christmas edition, living legacy, holiday celebration, family photos, christmas tree" />
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $customTitle ? $customTitle . ' - ' . $profileName : $profileName . ' - Christmas Memories' }}" />
    <meta property="og:description" content="🎄 Christmas Memories with {{ $profileName }} - Share and celebrate special moments this holiday season. Create lasting memories with family and friends." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ $profile->profile_picture ?: '/images/logo/logo-dark.png' }}" />
    <meta property="og:image:alt" content="🎄 Christmas Memories - {{ $currentYear }} Holiday Edition" />
    <meta property="og:site_name" content="Living Legacy - Christmas Edition" />
    
    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $customTitle ? $customTitle . ' - ' . $profileName : $profileName . ' - Christmas Memories' }}" />
    <meta name="twitter:description" content="🎄 Christmas Memories with {{ $profileName }} - Share and celebrate special moments this holiday season. Create lasting memories with family and friends." />
    <meta name="twitter:image" content="{{ $profile->profile_picture ?: '/images/logo/logo-dark.png' }}" />
    
    <link href="/public/logo.png" rel="icon" type="image/png" />
    <meta href="/public/logo.png" name="apple-touch-icon" />
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
</head>
<body>
    <div id="root"></div>
    <script src="/src/main.jsx" type="module"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        const global = window;
    </script>
</body>
</html>


