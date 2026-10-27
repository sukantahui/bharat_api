<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title ?? 'JSON Viewer' }}</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    body {
        background: #0f172a;
        color: #e5e7eb;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 14px;
    }
    .container {
        padding: 20px;
    }
    .key { color: #60a5fa; }
    .string { color: #a7f3d0; }
    .number { color: #facc15; }
    .bool { color: #f87171; }
    .null { color: #9ca3af; font-style: italic; }
    .toggle {
        cursor: pointer;
        user-select: none;
        color: #94a3b8;
    }
    .indent {
        padding-left: 18px;
        border-left: 1px dashed #334155;
        margin-left: 6px;
    }
    .collapsed > .content {
        display: none;
    }
</style>
</head>
<body>

<div class="container">
    <h3 style="margin-bottom: 16px;">{{ $title }}</h3>

    @include('universal.partials.json-renderer', ['data' => $data])
</div>

<script>
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('toggle')) {
        e.target.parentElement.classList.toggle('collapsed');
    }
});
</script>

</body>
</html>
