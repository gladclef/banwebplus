package scraping;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;

import structure.Semester;
import structure.Subject;

/**
 * Will scrape the front page of the banweb site to determine the available
 * semesters and subjects.
 */
public class Availabilities {
	/** URL to the front page of banweb. */
	private String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.P_UncgSrchCrsOff";
	/** The available semesters. */
	protected List<Semester> semesters = new ArrayList<>();
	/** The available subjects. */
	protected List<Subject> subjects = new ArrayList<>();

	public Availabilities(String baseurl) {
		this.baseurl = baseurl;
	}

	/**
	 * Scrapes the front page of the banweb site to update the semesters and
	 * subjects listing.
	 * 
	 * @throws IOException
	 *             From {@link Jsoup#connect(String)}
	 */
	public void scrape() throws IOException {
		// get the page
		Document document = Jsoup.connect(baseurl).get();

		// find the semesters
		scrapeSemesters(document);

		// find the subjects
		scrapeSubjects(document);
	}

	/**
	 * @return An unmodifiable set of available semesters.
	 */
	public List<Semester> getSemesters() {
		return semesters;
	}

	/**
	 * @return An unmodifiable set of available subjects.
	 */
	public List<Subject> getSubjects() {
		return subjects;
	}

	/**
	 * Scrapes the given {@link Document} to find all available {@link Semester}
	 * s.
	 * 
	 * @param document
	 *            The front page of the banweb course offering.
	 * @throws IOException
	 *             If {@link Jsoup#connect(String)} throws an exception.
	 */
	protected void scrapeSemesters(Document document) throws IOException {
		// find the selector
		Element selector = document.getElementsByAttributeValue("name", "p_term").first();

		// get each semester in the selector
		for (Element option : selector.getElementsByTag("option")) {
			String value = option.attr("value");
			semesters.add(new Semester(value));
		}

		// they post summer and fall at the same time, usually
		Semester lastSemester = semesters.get(semesters.size() - 1);
		int possibleNextSemesters = (lastSemester.getSemesterName().equals("Spring")) ? 2 : 1;

		// try to add the next semester, when not yet posted, so as to give
		// a peek preview
		for (int nextSemestersCount = 0; nextSemestersCount < possibleNextSemesters; nextSemestersCount++) {
			if (isNextSemesterAvailable()) {
				// get the next semester
				lastSemester = semesters.get(semesters.size() - 1);
				Semester unpostedSemester = new Semester(lastSemester.getPredictedNext());

				// add the next semester
				semesters.add(unpostedSemester);
			}
		}
	}

	/**
	 * Scrapes the given {@link Document} to find all available {@link Subject}
	 * s.
	 * 
	 * @param document
	 *            The front page of the banweb course offering.
	 */
	protected void scrapeSubjects(Document document) {
		// find the selector
		Element selector = document.getElementsByAttributeValue("name", "p_subj").first();

		// get each semester in the selector
		for (Element option : selector.getElementsByTag("option")) {
			String shortName = option.attr("value");
			String longName = option.ownText();
			subjects.add(new Subject(shortName, longName));
		}
	}

	/**
	 * Tries to determine if the next semester is available and is just being
	 * hidden by banweb.
	 * 
	 * @return True if the next semester is available after the last of the
	 *         currently known {@link semesters}.
	 * @throws IOException
	 *             If {@link Jsoup#connect(String)} throws an exception.
	 */
	protected boolean isNextSemesterAvailable() throws IOException {

		// ask it for what should be next
		Semester lastSemester = semesters.get(semesters.size() - 1);
		String nextSemesterString = lastSemester.getPredictedNext();

		// Try to find the course offerings page for (that semester + math).
		// Note: math just seems to be more reliable.
		try {
			OfferingsScraper offeringsScraper = new OfferingsScraper(new Semester(nextSemesterString),
					new Subject("MATH", "Mathematics"));
			offeringsScraper.scrape();
		} catch (NoCoursesException e) {
			return false;
		}

		return true;
	}
}
