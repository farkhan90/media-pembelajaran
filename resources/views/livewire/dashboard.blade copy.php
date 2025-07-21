<div>
    <!-- HEADER -->
    <x-header title="Hello" separator progress-indicator>
    </x-header>

    <!-- TABLE  -->
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat title="Role Anda" value="{{ auth()->user()->role }}" icon="o-user" />
            <x-stat title="Total User" value="{{ \App\Models\User::count() }}" icon="o-users" />
            <x-stat title="Total Sekolah" value="0" icon="o-building-office-2" />
        </div>
    </div>
</div>
