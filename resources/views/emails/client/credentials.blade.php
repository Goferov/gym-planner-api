@component('mail::message')
    # Witaj, {{ $client->name }}!

    Twój trener założył Ci konto w aplikacji **Gym Planner**.

    @component('mail::panel')
        **Login:** {{ $client->email }}
        **Hasło:** {{ $plainPassword }}
    @endcomponent

    Po pierwszym logowaniu możesz zmienić hasło w zakładce **Ustawienia**.

    Dobrej pracy!
    {{ config('app.name') }}
@endcomponent
