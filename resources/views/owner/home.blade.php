<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kosync - Pemilik</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
    <main class="simple-page">
      <section class="panel compact-panel">
        <p class="eyebrow">Pemilik Kos</p>
        <h1>{{ $kos?->name ?? 'Kos belum dibuat' }}</h1>
        <p>Data pemilik sudah aktif. Scope aplikasi ini berfokus pada flow penghuni.</p>
        <p>Total kamar: <strong>{{ $kos?->rooms->count() ?? 0 }}</strong></p>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="ghost-button" type="submit">Logout</button>
        </form>
      </section>
    </main>
  </body>
</html>
