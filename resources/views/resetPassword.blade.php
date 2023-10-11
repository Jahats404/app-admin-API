<form method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $user[0]['id'] }}">
    <input type="password" name="password" placeholder="New Password">
    <input type="password" name="password_confirmation" placeholder="Confirm Password">
    <br>
    <br>
    <button type="submit"></button>
</form>