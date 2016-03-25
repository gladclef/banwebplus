package system.io;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.Collection;
import java.util.Date;

import structure.Clazz;
import structure.Semester;
import structure.Subject;

/**
 * For interfacing with system files. This class is, in part, meant to abstract
 * away the file system and so also the file paths.
 */
public class FileInterface implements SystemInterface {
	BufferedReader readHandle = null;
	BufferedWriter writeHandle = null;

	/**
	 * Used internally to initialize this instance when trying to save or some
	 * other I/O.
	 * <p>
	 * Will create a new file to write to if isWritable and a file doesn't
	 * already exist.
	 * <p>
	 * Do not include the file path in the tableName.
	 * 
	 * @param fileName
	 *            The name of the file to open with.
	 * @param isWritable
	 *            If true, will open/create an existing/new file to write to
	 *            with the given fileName. Otherwise, will attempt to read a
	 *            file.
	 * @throws IOException
	 *             If opening the file failed.
	 */
	protected void openFile(String fileName, boolean isWritable) throws IOException {
		File file = new File(getPath() + fileName);
		if (isWritable) {
			writeHandle = new BufferedWriter(new FileWriter(file));
		} else {
			readHandle = new BufferedReader(new FileReader(file));
		}
	}

	/**
	 * Closes any open file handles.
	 * 
	 * @throws IOException
	 *             If there is an issue closing the handle.
	 */
	protected void close() throws IOException {
		if (writeHandle != null) {
			writeHandle.close();
		}
		if (readHandle != null) {
			readHandle.close();
		}
		writeHandle = null;
		readHandle = null;
	}

	/**
	 * @return The standard path to all scraped files.
	 */
	protected String getPath() {
		File executionDirectory = new File(System.getProperty("user.dir"));
		return executionDirectory.getParent() + File.separator;
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

	@Override
	public void saveSemester(Semester semester, Subject subject, Collection<Clazz> classes) throws IOException {

	}

	@Override
	public boolean isSemesterCached(Semester semester) throws IOException {
		try {
			openFile(getFileNameForSemester(semester), false);
		} catch (IOException e) {
			close();
			return false;
		}
		close();
		return true;
	}

	@Override
	public void saveSemestersAndSubjects(Collection<Semester> semesters, Collection<Subject> subjects)
			throws IOException {

		// open the file for writing
		openFile("banweb_terms.php", true);

		// add the boiler plate php code
		writeHandle.write(getWaterMark());
		writeHandle.write("$terms = [\n");

		// add each semester as a value to "terms"
		String lineEnding = "";
		boolean first = true;
		for (Semester semester : semesters) {
			// write the current semester to file
			writeHandle.write(String.format("%s    [\"%s\", \"%s %s\"]", lineEnding,
					semester.getCode(), semester.getSemesterName(), semester.getCalendarYear()));

			// get ready for semesters 2 .. n
			if (first) {
				first = false;
				lineEnding = ",\n";
			}
		}
		
		// close the php code
		writeHandle.write("\n];\n");

		close();
	}

	protected String getWaterMark() {
		return String.format(
				"// generated with the %s.java class at %s\n",
				this.getClass().getSimpleName(),
				(new Date()).toString());
	}
}
