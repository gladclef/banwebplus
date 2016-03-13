package scraping;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Map;
import java.util.Map.Entry;

public class Connection {
	/**
	 * Executes a GET request to the given targetURL with the given
	 * urlParameters, where each parameter is correctly spaced with "?", "&",
	 * and "=".
	 * 
	 * @param targetURL
	 *            The URL to connect to.
	 * @param urlParameters
	 *            The parameters to pass on the URL.
	 * @return The returned value from the server.
	 * @throws IOException
	 *             If something doesn't work along the way.
	 */
	public static String executeGet(String targetURL, Map<String, String> urlParameters) throws IOException {
		return executeConnection("GET", targetURL, urlParameters);
	}

	/**
	 * Executes a POST request to the given targetURL with the given
	 * urlParameters, where each parameter is correctly spaced with "?", "&",
	 * and "=".
	 * 
	 * @param targetURL
	 *            The URL to connect to.
	 * @param urlParameters
	 *            The parameters to pass on the URL.
	 * @return The returned value from the server.
	 * @throws IOException
	 *             If something doesn't work along the way.
	 */
	public static String executePost(String targetURL, Map<String, String> urlParameters) throws IOException {
		return executeConnection("POST", targetURL, urlParameters);
	}

	/**
	 * @param type
	 *            The type of the connection (POST or GET)
	 */
	private static String executeConnection(String type, String targetURL, Map<String, String> urlParameters)
			throws IOException {
		HttpURLConnection connection = null;
		String urlParametersAsString = urlParamsToList(urlParameters);

		try {
			// Create connection
			URL url = new URL(targetURL);
			connection = (HttpURLConnection) url.openConnection();
			connection.setRequestMethod(type);
			connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

			connection.setRequestProperty("Content-Length", Integer.toString(urlParametersAsString.getBytes().length));
			connection.setRequestProperty("Content-Language", "en-US");

			connection.setUseCaches(false);
			connection.setDoOutput(true);

			// Send request
			DataOutputStream wr = new DataOutputStream(connection.getOutputStream());
			wr.writeBytes(urlParametersAsString);
			wr.close();

			// Get Response
			InputStream is = connection.getInputStream();
			BufferedReader rd = new BufferedReader(new InputStreamReader(is));
			StringBuilder response = new StringBuilder();
			String line;
			while ((line = rd.readLine()) != null) {
				response.append(line);
				response.append('\n');
			}
			rd.close();
			return response.toString();
		} finally {
			if (connection != null) {
				connection.disconnect();
			}
		}
	}

	/**
	 * Format the given urlParams using the standard "?", "&", and "=".
	 * <p>
	 * For example, the map<br>
	 * (("a","1"),("b","2"))<br>
	 * would become<br>
	 * "?a=1&b=2"
	 * 
	 * @param urlParams
	 *            the parameters to format.
	 * @return The string representing the formated parameters.
	 */
	private static String urlParamsToList(Map<String, String> urlParams) {
		StringBuilder retval = new StringBuilder();

		retval.append("?");

		boolean first = true;
		for (Entry<String, String> entry : urlParams.entrySet()) {
			if (first) {
				first = false;
			} else {
				retval.append("&");
			}

			retval.append(entry.getKey());
			retval.append("=");
			retval.append(entry.getValue());
		}

		return retval.toString();
	}
}
