package scraping;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import structure.Clazz;
import structure.Semester;
import structure.SemesterAndSubjectCourses;
import structure.Subject;

/**
 * Scrapes the offered courses for the desired semester + subject.
 */
public class OfferingsScraper {
	private static final int MIN_CLASS_ATTRIBUTES_SIZE = 2;
	/** The string to look for if the page returns no courses. */
	private static final String noCoursesText = "No courses found matching your term and subject";
	/** The base url for loading courses from a page. */
	public static final String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.P_UncgSrchCrsOff";
	/** The semester registered to load courses for. */
	Semester semester = null;
	/** The subject registered to load courses for. */
	Subject subject = null;
	/** The page of classes */
	SemesterAndSubjectCourses classes = null;

	/**
	 * @param semester
	 *            The semester to look for.
	 * @param subject
	 *            The subject to look for.
	 */
	public OfferingsScraper(Semester semester, Subject subject) {
		this.semester = semester;
		this.subject = subject;
		this.classes = new SemesterAndSubjectCourses(semester, subject);
	}

	/**
	 * Scrapes for the desired semester + subject.
	 * 
	 * @throws IOException
	 *             If there is an exception from {@link Jsoup#connect(String)}.
	 * @throws NoCoursesException
	 *             If no courses can be found for the registered semester +
	 *             subject.
	 */
	public void scrape() throws IOException, NoCoursesException {
		// variables to load the page
		Map<String, String> pageVars = new HashMap<>();
		pageVars.put("p_term", semester.getCode());
		pageVars.put("p_subj", subject.getShortName());

		// get the page
		Document page = Jsoup.connect(baseurl + ConnectionUtils.urlParamsToList(pageVars)).get();

		// verify the page has courses
		for (Element element : page.getElementsByTag("h2")) {
			if (element.ownText().equals(noCoursesText)) {
				throw new NoCoursesException(semester, subject);
			}
		}

		// parse out the classes
		parseClasses(page);
	}
	
	/**
	 * @return The reference to this instance's courses object.
	 */
	public SemesterAndSubjectCourses getScrapedCourses()
	{
		return classes;
	}

	/**
	 * Parses the classes on the given page and inserts them into this
	 * instance's SemesterAndSubjectCourses object.
	 * 
	 * @param page
	 *            The {@link Document} loaded off the web.
	 */
	protected void parseClasses(Document page) {
		// find the table element
		Element table = page.getElementsByTag("table").first();

		// get all of the rows, to be interpretted as classes
		Elements rows = table.getElementsByTag("tr");

		// go through each row and try to load it as a clazz
		for (Element row : rows) {
			parseClass(row);
		}
	}

	/**
	 * Parses the given row as if it were a class and, if successful, adds it to
	 * the {@link #classes}.
	 * 
	 * @param row
	 *            The table row to try and interpret as a class.
	 * @return true if a class, false if empty or a header row
	 */
	protected boolean parseClass(Element row) {
		
		// get the table cells
		Elements cells = row.getElementsByTag("td");
		
		// verify has enough contents to count as a class
		if (cells.size() < MIN_CLASS_ATTRIBUTES_SIZE)
		{
			return false;
		}
		
		// it is probably a class?
		Clazz clazz = new Clazz();
		int filledCellCount = 0;
		int cellIndex = -1;
		for (Element cell : cells)
		{
			cellIndex++;
			
			// verify the cell has contents
			String value = cell.ownText().trim();
			if (value.equals(""))
			{
				continue;
			}
			filledCellCount++;
			
			// add the contents to the clazz
			Clazz.ClassAttribute attribute = Clazz.ClassAttribute.values()[cellIndex];
			clazz.addAttribute(attribute, value);
		}
		
		// verify is a class by its filled cells count
		if (filledCellCount > MIN_CLASS_ATTRIBUTES_SIZE)
		{
			// is a class!
			
			// fill in the subject
			clazz.addAttribute(Clazz.ClassAttribute.Subject, subject.getShortName());
			
			// add it!
			classes.addClass(clazz);
		}
		else
		{
			return false;
		}
		
		return true;
	}
}
