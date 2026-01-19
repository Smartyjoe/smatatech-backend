<x-guest-layout>
    <form method="POST" action="/admin/login">
        @csrf

        <div>
            <x-input-label value="Email" />
            <x-text-input name="email" type="email" required />
        </div>

        <div class="mt-4">
            <x-input-label value="Password" />
            <x-text-input name="password" type="password" required />
        </div>

        <button class="mt-6 w-full btn-primary">
            Admin Login
        </button>
    </form>
</x-guest-layout>
