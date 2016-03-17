package scraping;

import java.io.IOException;
import java.util.Collection;
import java.util.TreeMap;
import java.util.TreeSet;

import structure.Semester;
import structure.SemesterAndSubjectCourses;
import structure.Subject;
import system.io.FileInterface;
import system.io.SemesterIO;

public class Main {
	/**
	 * The number of latest semesters to always scrape, even if already cached.
	 */
	private static final int NUM_SEMESTERS_ALWAYS_SCRAPED = 5;
	/** The URL to look for available semester data at. */
	public static final String baseurl = "https://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff";

	public static void main(String[] args) throws IOException {
		Availabilities availabilities = new Availabilities(baseurl);

		// get the available semesters from banweb
		availabilities.scrape();
		printSemesters(availabilities.getSemesters());

		// figure out which semesters are missing
		TreeSet<Semester> semestersToScrape = getUnchachedOrRecentSemesters(availabilities.getSemesters());

		// scrape those semesters
		TreeMap<Semester, TreeMap<Subject, SemesterAndSubjectCourses>> scrapedSemesters = new TreeMap<>();
		scrapeSemesters(semestersToScrape, availabilities.getSubjects(), scrapedSemesters);
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
			TreeMap<Semester, TreeMap<Subject, SemesterAndSubjectCourses>> scrapedSemesters) throws IOException {
		System.out.println("Scraping semesters from Banweb...");

		// scrape each semester in turn
		for (Semester semester : semestersToScrape) {

			System.out.println(String.format("%s %s", semester.getSemesterName(), semester.getCalendarYear()));

			// create the semester instance and insert it into the returned
			// value
			TreeMap<Subject, SemesterAndSubjectCourses> scrapedSemester = new TreeMap<>();
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
	 */
	private static TreeSet<Semester> getUnchachedOrRecentSemesters(TreeSet<Semester> semesters) {
		TreeSet<Semester> retval = new TreeSet<>();

		// start by adding all semesters in the last five semesters
		Semester nextLast = semesters.last();
		for (int i = 0; i < NUM_SEMESTERS_ALWAYS_SCRAPED; i++) {
			retval.add(nextLast);
			nextLast = semesters.lower(nextLast);
		}

		// look for semesters not yet cached
		SemesterIO semesterChecker = new SemesterIO(FileInterface.class);
		while (nextLast != null) {

			// check if the semester is cached
			if (!semesterChecker.isSemesterCached(nextLast)) {
				retval.add(nextLast);
			}

			nextLast = semesters.lower(nextLast);
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
		System.out.println("Available semesters for download: ");
		for (Semester semester : semesters) {
			System.out.println(semester);
		}
		System.out.println("");
	}
}
