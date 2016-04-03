package main.java.system.io;

import java.io.IOException;
import java.lang.reflect.InvocationTargetException;
import java.util.LinkedHashMap;
import java.util.List;

import main.java.structure.Semester;
import main.java.structure.SemesterAndSubjectCourses;
import main.java.structure.Subject;

/**
 * Defines methods to perform I/O on Semester related functionality, including
 * saving classes for an entire semester.
 */
public class SemesterIO {
	/** The preferred instance type used to interact with the system. */
	Class<? extends SystemInterface> systemInterfaceClass = null;

	/**
	 * @param preferredSystemInterfaceClass
	 *            A {@link #SystemInterface} class with an argument-less
	 *            constructor.
	 * @throws IllegalArgumentException
	 *             If the given preferredSystemInterfaceClass is invalid.
	 */
	public SemesterIO(Class<? extends SystemInterface> preferredSystemInterfaceClass) throws IllegalArgumentException {

		// verify the arguments
		try {
			preferredSystemInterfaceClass.getConstructor();
		} catch (NoSuchMethodException e) {
			throw new IllegalArgumentException(
					"The preferredSystemInterfaceClass must have an argument-less constructor.", e);
		}

		this.systemInterfaceClass = preferredSystemInterfaceClass;
	}

	/**
	 * Check if the given semester is already downloaded and cached.
	 * 
	 * @param semester
	 *            The semester to check for.
	 * @return True if a file exists for the given semester. False otherwise.
	 * @throws IllegalStateException
	 *             If the interface to the system can't instantiate (probably
	 *             won't happen).
	 * @throws IOException
	 *             If there is an issue checking with the System if the semester
	 *             is cached.
	 */
	public boolean isSemesterCached(Semester semester) throws IllegalStateException, IOException {

		// open an interface
		SystemInterface interfaceInstance = getInterfaceInstance();

		// test if the semester is cached
		return interfaceInstance.isSemesterCached(semester);
	}

	/**
	 * Creates a new instance of the desired {@link #systemInterfaceClass} to
	 * perform I/O with.
	 * 
	 * @return The new I/O instance.
	 * @throws IllegalStateException
	 *             If creating the instance failed for some reason.
	 */
	protected SystemInterface getInterfaceInstance() throws IllegalStateException {
		try {
			return systemInterfaceClass.getConstructor().newInstance();
		} catch (InstantiationException | IllegalAccessException | IllegalArgumentException | InvocationTargetException
				| NoSuchMethodException | SecurityException e) {
			throw new IllegalStateException("Can't instantiate SystemInteface class " + systemInterfaceClass);
		}
	}

	/**
	 * Save the given scrapedSemesters. Also saves the other given semesters and
	 * subjects as if available.
	 * 
	 * @param scrapedSemesters
	 *            The semesters that were scraped, and their scraped data.
	 * @param semesters
	 *            All available semesters, including those not scraped.
	 * @param subjects
	 *            All available subjects, including those not scraped.
	 * @throws IOException
	 *             If saving fails.
	 */
	public void saveSemesters(
			LinkedHashMap<Semester, LinkedHashMap<Subject, SemesterAndSubjectCourses>> scrapedSemesters,
			List<Semester> semesters, List<Subject> subjects) throws IOException {
		SystemInterface interfaceInstance = getInterfaceInstance();

		// save all available semesters/subjects
		interfaceInstance.saveSemestersAndSubjects(semesters, subjects);

		// save the scraped semesters individually
		for (Semester semester : scrapedSemesters.keySet()) {
			interfaceInstance.saveSemester(semester, scrapedSemesters.get(semester));
		}
	}
}
