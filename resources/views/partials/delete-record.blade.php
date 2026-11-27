@if(auth()->user()?->canDeleteAnyRecord())
    @php
        $deleteTable = $deletableRecord->getTable();
        $deleteKey = [$deletableRecord->getKeyName() => $deletableRecord->getKey()];
        $deleteValues = (array) \Illuminate\Support\Facades\DB::table($deleteTable)->where($deleteKey)->first();
        $deleteToken = \Illuminate\Support\Facades\Crypt::encryptString(json_encode([
            'table' => $deleteTable,
            'actor_id' => auth()->id(),
            'key' => $deleteKey,
            'fingerprint' => hash('sha256', serialize($deleteValues)),
            'return_to' => request()->getRequestUri(),
        ], JSON_THROW_ON_ERROR));
    @endphp
    <form method="POST" action="{{ route('records.destroy', ['table' => $deleteTable]) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('¿Eliminar permanentemente este registro y sus relaciones en cascada?');">
        @csrf
        @method('DELETE')
        <input type="hidden" name="record" value="{{ $deleteToken }}">
        <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-trash" aria-hidden="true"></i> Eliminar</button>
    </form>
@endif
