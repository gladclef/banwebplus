package main.java.scraping;

import java.io.IOException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.List;

import main.java.structure.Semester;
import main.java.structure.SemesterAndSubjectCourses;
import main.java.structure.Subject;
import main.java.system.io.FileInterface;
import main.java.system.io.SemesterIO;

public class Main {
	/**
	 * The number of latest semesters to always scrape, even if already cached.
	 */
	private static final int NUM_SEMESTERS_ALWAYS_SCRAPED = 5;
	/** The URL to look for available semester data at. */
	public static final String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff";
	/** The semester saver to use for this program. */
	private static SemesterIO semesterSaver = new SemesterIO(FileInterface.class);

	public static void main(String[] args) throws IOException {
		Availabilities availabilities = new Availabilities(baseurl);

		// get the available semesters from banweb
		availabilities.scrape();
		System.out.println("Available semesters for download: ");
		printSemesters(availabilities.getSemesters());

		// figure out which semesters are missing
		List<Semester> semestersToScrape = filterForUncachedOrRecentSemesters(availabilities.getSemesters());
		System.out.println("Semesters to be scraped: ");
		printSemesters(semestersToScrape);

		// scrape those semesters, one at a time, and same them
		for (Semester semester : semestersToScrape)
		{
			List<Semester> singleSemesterList = new ArrayList<>(1);
			singleSemesterList.add(semester);

			// scrape the single semester
			LinkedHashMap<Semester, LinkedHashMap<Subject, SemesterAndSubjectCourses>> scrapedSemesters = new LinkedHashMap<>();
			scrapeSemesters(singleSemesterList, availabilities.getSubjects(), scrapedSemesters);

			// save the scraped data
			semesterSaver.saveSemesters(scrapedSemesters, availabilities.getSemesters(), availabilities.getSubjects());
		}
	}

	/**
	 * Scrapes the given semestersToScrape + subjectsToScrape and stores the
	 * results in the given scrapedSemesters map.
	 * 
	 * @param semestersToScrape
	 *            The collection of semesters to scrape classes for.
	 * @param subjectsToScrape
	 *            The subjects to scrape for each of the given
	 *            semestersToScrape.
	 * @param scrapedSemesters
	 *            The SemesterAndSubjectCourses will be put here.
	 * @throws IOException
	 *             If the scraper has an issue connecting to the server.
	 */
	private static void scrapeSemesters(Collection<Semester> semestersToScrape, Collection<Subject> subjectsToScrape,
			LinkedHashMap<Semester, LinkedHashMap<Subject, SemesterAndSubjectCourses>> scrapedSemesters)
					throws IOException {
		System.out.println("Scraping semesters from Banweb...");

		// scrape each semester in turn
		for (Semester semester : semestersToScrape) {

			System.out.println(String.format("%s %s", semester.getSemesterName(), semester.getCalendarYear()));

			// create the semester instance and insert it into the returned
			// value
			LinkedHashMap<Subject, SemesterAndSubjectCourses> scrapedSemester = new LinkedHashMap<>();
			scrapedSemesters.put(semester, scrapedSemester);

			// scrape each subject in turn
			for (Subject subject : subjectsToScrape) {

				// create the scraper and scrape
				OfferingsScraper scraper = new OfferingsScraper(semester, subject);
				try {
					scraper.scrape();
					System.out.println(String.format("scraped %3d courses:  %s",
							scraper.getScrapedCourses().getClasses().size(), subject.getLongName()));
				} catch (NoCoursesException e) {
					// TODO log only when verbose
					System.out.println(String.format("...subj --0 courses:  %s", subject.getLongName()));
				} catch (IOException e) {
					throw new IOException("Can't scrape from Banweb!", e);
				}

				// add the scraped classes to the return value
				scrapedSemester.put(subject, scraper.getScrapedCourses());
			}

			System.out.println("");
		}

		System.out.println("");
	}

	/**
	 * Returns a subset of the given semesters that only includes semesters that
	 * have not been cached/have occured recently.
	 * 
	 * @param semesters
	 *            The semesters to pick from.
	 * @return The semesters that should probably be scraped.
	 * @throws IOException
	 *             If there is an issue checking for cached semesters on the
	 *             System.
	 * @throws IllegalStateException
	 *             If the interface to the System can't be initiated.
	 */
	private static List<Semester> filterForUncachedOrRecentSemesters(List<Semester> semesters)
			throws IllegalStateException, IOException {
		List<Semester> retval = new ArrayList<>();
		
		// get the semesters in reverse order
		List<Semester> reverse = Collections.reverse(new ArrayList<>(semesters));

		// start by adding all semesters in the last five semesters
		for (int i = 0; i < NUM_SEMESTERS_ALWAYS_SCRAPED; i++) {
			retval.add(reverse.get(i));
			reverse.remove(i);
		}

		// look for semesters not yet cached
		for (Semester semester : reverse)
		{
			// check if the semester is cached
			if (!semesterSaver.isSemesterCached(semester)) {
				retval.add(semester);
			}
		}

		return retval;
	}

	/**
	 * Prints the given semesters to stdout, with the implication that these are
	 * the semesters available on Banweb.
	 * 
	 * @param semesters
	 *            The semesters to list.
	 */
	private static void printSemesters(Collection<Semester> semesters) {
		for (Semester semester : semesters) {
			System.out.println(semester);
		}
		System.out.println("");
	}
}
