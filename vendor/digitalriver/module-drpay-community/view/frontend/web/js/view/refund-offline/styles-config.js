define([], function () {
    return {
        "style": {
            "base": {
                "padding": "0",
                "color": "#495057",
                "height": "35px",
                "fontSize": "1rem",
                "fontFamily": "apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif",
                ":hover": {
                    "color": "#137bee"
                },
                "::placeholder": {
                    "color": "#999"
                },
                ":-webkit-autofill": {
                    "color": "purple"
                },
                ":focus": {
                    "color": "blue"
                }
            },
            "invalid": {
                "color": "#dc3545",
                ":-webkit-autofill": {
                    "color": "#dc3545"
                }
            },
            "complete": {
                "color": "#28a745",
                ":hover": {
                    "color": "#28a745"
                },
                ":-webkit-autofill": {
                    "color": "#28a745"
                }
            },
            "empty": {
                "color": "#000000"
            }
        },
    }
});
