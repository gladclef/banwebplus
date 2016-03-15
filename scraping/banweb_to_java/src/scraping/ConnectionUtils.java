package scraping;

import java.util.Map;
import java.util.Map.Entry;

public class ConnectionUtils {
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
	public static String urlParamsToList(Map<String, String> urlParams) {
		StringBuilder retval = new StringBuilder();

		boolean first = true;
		for (Entry<String, String> entry : urlParams.entrySet()) {
			if (first) {
				first = false;
				retval.append("?");
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
