package system.io;

import java.io.IOException;
import java.lang.reflect.InvocationTargetException;

import structure.Semester;

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
	 */
	public boolean isSemesterCached(Semester semester) throws IllegalStateException {

		// open an interface
		SystemInterface interfaceInstance = null;
		try {
			interfaceInstance = systemInterfaceClass.getConstructor().newInstance();
		} catch (InstantiationException | IllegalAccessException | IllegalArgumentException | InvocationTargetException
				| NoSuchMethodException | SecurityException e) {
			throw new IllegalStateException("Can't instantiate SystemInteface class " + systemInterfaceClass);
		}

		// Attach the interface to the semester.
		// If this fails, then the semester is not cached.
		try {
			interfaceInstance.initialize(getTableName(semester), false);
		} catch (IOException e) {
			return false;
		}
		return true;
	}

	/**
	 * Get the name of the table to use to perform I/O on the given semester
	 * with the preferred {@link #systemInterfaceClass}.
	 * 
	 * @param semester
	 *            The semester to perform I/O for.
	 * @return The name of the table to perform I/O on, or the name of the file
	 *         to read/write from. ("sem_yyyyii.php" for files, "name_yyyy" for
	 *         anything else, where "ii" is the semester index as defined by
	 *         banweb)
	 */
	protected String getTableName(Semester semester) {
		if (systemInterfaceClass.equals(FileInterface.class)) {
			return getFileNameForSemester(semester);
		}
		return String.format("%s_%d", semester.getSemesterName(), semester.getCalendarYear());
	}

	/**
	 * Get the name of the file to read from/write to for the given semester.
	 * 
	 * @param semester
	 *            The semester to interact I/O with.
	 * @return The name of the file used to cache the scraped semester.
	 */
	protected String getFileNameForSemester(Semester semester) {
		return String.format("sem_%d%d.php", semester.getSchoolYear(), semester.getSemesterIndex());
	}
}
