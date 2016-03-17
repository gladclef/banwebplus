package system.io;

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
		SystemInterface interfaceInstance = getInterfaceInstance();

		// test if the semester is cached
		return interfaceInstance.isSemesterCached(semester);
	}

	/**
	 * Creates a new instance of the desired {@link #systemInterfaceClass} to
	 * perform I/O with.
	 * 
	 * @return The new I/O instance.
	 * @throws IllegalStateException If creating the instance failed for some reason.
	 */
	protected SystemInterface getInterfaceInstance() throws IllegalStateException {
		try {
			return systemInterfaceClass.getConstructor().newInstance();
		} catch (InstantiationException | IllegalAccessException | IllegalArgumentException | InvocationTargetException
				| NoSuchMethodException | SecurityException e) {
			throw new IllegalStateException("Can't instantiate SystemInteface class " + systemInterfaceClass);
		}
	}
}
