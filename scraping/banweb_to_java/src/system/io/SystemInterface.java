package system.io;

import java.io.IOException;
import java.util.Collection;

import structure.Clazz;
import structure.Semester;
import structure.Subject;

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
	 * @param subject
	 *            The associated subject of the semester.
	 * @param classes
	 *            The classes of the semester.
	 * @throws IOException
	 *             If there is an issue saving any of the data.
	 */
	public void saveSemester(Semester semester, Subject subject, Collection<Clazz> classes) throws IOException;
	
	/**
	 * Determines if the given semester is cached somewhere.
	 * 
	 * @param semester The semester to check for.
	 * @return True if cached, false otherwise.
	 */
	public boolean isSemesterCached(Semester semester);
}
