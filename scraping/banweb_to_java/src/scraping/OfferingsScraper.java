package scraping;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;

import structure.Semester;
import structure.Subject;

/**
 * Scrapes the offered courses for the desired semester + subject.
 */
public class OfferingsScraper {
	/** The string to look for if the page returns no courses. */
	private static final String noCoursesText = "No courses found matching your term and subject";
	/** The base url for loading courses from a page. */
	public static final String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.P_UncgSrchCrsOff";
	/** The semester registered to load courses for. */
	Semester semester = null;
	/** The subject registered to load courses for. */
	Subject subject = null;

	/**
	 * @param semester
	 *            The semester to look for.
	 * @param subject
	 *            The subject to look for.
	 */
	public OfferingsScraper(Semester semester, Subject subject) {
		this.semester = semester;
		this.subject = subject;
	}

	/**
	 * Scrapes for the desired semester + subject.
	 * 
	 * @throws IOException
	 *             If there is an exception from {@link Jsoup#connect(String)}.
	 * @throws NoCoursesException If no courses can be found for the registered semester + subject.
	 */
	public void scrape() throws IOException, NoCoursesException {
		// variables to load the page
		Map<String, String> pageVars = new HashMap<>();
		pageVars.put("p_term", semester.getCode());
		pageVars.put("p_subj", subject.getShortName());

		// get the page
		Document page = Jsoup.connect(baseurl + Connection.urlParamsToList(pageVars)).get();
		
		// verify the page has courses
		for (Element element : page.getElementsByTag("h2"))
		{
			if (element.ownText().equals(noCoursesText))
			{
				throw new NoCoursesException(semester, subject);
			}
		}

		// parse out the classes
		// TODO
	}
}
