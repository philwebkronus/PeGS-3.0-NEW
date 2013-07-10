function AlphaNumericOnly(event)
        {
            var charCode = (event.which) ? event.which : event.keyCode;
            if (/*DisableWhiteSpaces*/(charCode == 32) || /*DisableSpecialCharacters*/ (charCode > 32 && charCode < 48) || (charCode > 57 && charCode < 65) || (charCode > 90 && charCode < 97) || (charCode > 122 && charCode < 128))
                return false;

            return true;
        }