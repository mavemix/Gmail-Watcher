# Gmail-Watcher
This project gets emails from a Gmail account, through its API.

## Prerequisites

### Enable APIs for your project

Any application that calls Google APIs needs to enable those APIs in the API Console. To enable the appropriate APIs for your project:

1.  Open the [Library](https://console.developers.google.com/apis/library) page in the API Console.
2.  Select the project associated with your application. Create a project if you do not have one already.
3.  Use the **Library** page to find Gmail API that the application will use and enable it for your project.

### Create authorization credentials

Any application that uses OAuth 2.0 to access Google APIs must have authorization credentials that identify the application to Google's OAuth 2.0 server. The following steps explain how to create credentials for your project. Your applications can then use the credentials to access APIs that you have enabled for that project.

1.  Open the [Credentials page](https://console.developers.google.com/apis/credentials) in the API Console.
2.  Click **Create credentials > OAuth client ID**.
3.  Complete the form. Set the application type to `Web application`. You must specify authorized **redirect URIs**. The redirect URIs are the endpoints to which the OAuth 2.0 server can send responses.

    For testing, you can specify URIs that refer to the local machine, such as `http://localhost:8080/index.php`.

    We recommend that you design your app's auth endpoints so that your application does not expose authorization codes to other resources on the page.

After creating your credentials, download from the API Console the credentials file & rename it to **credentials.json**. Securely store the file in the parent folder of the application (the file must be accessed & readable). You can also host this file in a different location, as long as you update in the GmailAuth class the constant: CONFIG['CREDENTIALS_FILE'] with the correct path.

> **Important:** Do not store the **credentials.json** file in a publicly-accessible location. In addition, if you share the source code to your application—for example, on GitHub—store the **credentials.json** file outside of your source tree to avoid inadvertently sharing your client credentials.
