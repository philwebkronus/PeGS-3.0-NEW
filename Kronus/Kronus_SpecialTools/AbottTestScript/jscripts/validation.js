function validateInputs(casinoName, username, password)
{
    if(casinoName != '') {
        if(username != ''){
            if(password != ''){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}