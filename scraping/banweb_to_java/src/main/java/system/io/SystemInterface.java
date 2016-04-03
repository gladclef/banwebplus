package main.java.system.io;

import java.io.IOException;
import java.util.Collection;
import java.util.Map;

import main.java.structure.Semester;
import main.java.structure.SemesterAndSubjectCourses;
import main.java.structure.Subject;

/**
 * Used to interact with the system, whether that is files, a database, etc...
 * <p>
 * Must define an argument-less constructor or define no constructors.
 */
public interface SystemInterface {
	/**
	 * Saves the given semester to the table this interface was
	 * {@link #initialize(String, boolean) initialized} with.
	 * 
	 * @param semester
	 *            The semester to save.
	 * @param subjectsAndClasses
	 *            The associated subjects and courses of the semester.
	 * @throws IOException
	 *             If there is an issue saving any of the data.
	 */
	public void saveSemester(Semester semester, Map<Subject, SemesterAndSubjectCourses> subjectsAndClasses) throws IOException;

	/**
	 * Will be called once by the {@link SemesterIO} class upon saving, in case
	 * the aggregate semester data without class data needs to be saved
	 * seperately from the individual semester data and classes.<br>
	 * The same applies for aggregate subject data.
	 * 
	 * @param semesters
	 *            The collection of all available semesters (including those
	 *            determined not to be re-saved because they were cached).
	 * @param subjects
	 *            The collection of all possible subjects.
	 * @throws IOException
	 *             If there is an issue saving the semesters or subjects.
	 */
	public void saveSemestersAndSubjects(Collection<Semester> semesters, Collection<Subject> subjects)
			throws IOException;

	/**
	 * Determines if the given semester is cached somewhere.
	 * 
	 * @param semester
	 *            The semester to check for.
	 * @return True if cached, false otherwise.
	 * @throws IOException If there is an issue checking if the semester has already been cached.
	 */
	public boolean isSemesterCached(Semester semester) throws IOException;
}
