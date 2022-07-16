<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    <title>Map manager</title>
</head>
<body>

<h1>Maps</h1>

@if ($errors->any())
    <blockquote class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </blockquote>
@endif

@if (session('success'))
    <blockquote class="alert alert-success">
        {{ session('success') }}
    </blockquote>
@endif

@if (session('error'))
    <blockquote class="alert alert-danger">
        {{ session('error') }}
    </blockquote>
@endif

<form action="{{ route('file.upload') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div>
        <input type="file" name="file" id="file" required accept=".bsp">
    </div>
    <div>
        <label for="config">Config File
            <select name="config" id="config">
                @foreach($configs as $config)
                    <option value="{{ $config }}">{{ basename($config) }}</option>
                @endforeach
            </select></label>
    </div>
    <div>
        <input type="submit" value="Upload">
    </div>
</form>

<h3>Maplist</h3>
<ul>
    @forelse($files as $file)
        @if(pathinfo($file, PATHINFO_EXTENSION) == 'bsp')
            <li>{{ basename($file, '.bsp') }}</li>
        @endif
    @empty
        <li>No files</li>
    @endforelse
</ul>

</body>
</html>
