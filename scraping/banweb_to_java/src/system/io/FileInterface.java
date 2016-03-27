package system.io;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.security.InvalidParameterException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.Date;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.TreeMap;

import structure.Clazz;
import structure.Semester;
import structure.SemesterAndSubjectCourses;
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

	protected String getWaterMark() {
		return String.format(
				"// generated with the %s.java class at %s\n",
				this.getClass().getSimpleName(),
				(new Date()).toString());
	}

	@Override
	public void saveSemestersAndSubjects(Collection<Semester> semesters, Collection<Subject> subjects)
			throws IOException {
		StringBuilder builder = new StringBuilder();

		// open the file for writing
		openFile("banweb_terms.php", true);
		builder.append(getWaterMark());

		// add the boiler plate php code
		builder.append("$terms = ");

		// add each semester to the list to be saved
		List<List<Object>> semestersList = new ArrayList<>(semesters.size());
		for (Semester semester : semesters) {
			
			// add the semester to the list
			semestersList.add(Arrays.asList(new Object[] {
					semester.getCode(),
					String.format("%s %s", semester.getSemesterName(), semester.getCalendarYear())
			}));
		}
		
		// append the list
		objToPHPString(semestersList, 1, builder);
		
		// close the php code
		builder.append(";\n");
		
		// write out the builder's value
		writeHandle.write(builder.toString());

		close();
	}

	@Override
	public void saveSemester(Semester semester, Map<Subject, SemesterAndSubjectCourses> subjectsAndClasses)
			throws IOException {
		StringBuilder builder = new StringBuilder();

		// open the semester handle for writing to
		openFile(getFileNameForSemester(semester), true);
		builder.append(getWaterMark());

		// start the boiler plate php code
		builder.append("$semesterData = ");
		Map<String, Object> semesterDataMap = new LinkedHashMap<>();

		// add the semester name
		semesterDataMap.put("name",
				String.format("%s %d", semester.getSemesterName(), semester.getCalendarYear()));
		
		// add the list of subjects and their abbreviations
		Map<String, String> subjectAbbreviations = new TreeMap<>();
		for (Subject subject : subjectsAndClasses.keySet())
		{
			subjectAbbreviations.put(subject.getShortName(), subject.getLongName());
		}
		semesterDataMap.put("subjects", subjectAbbreviations);
		
		// add the courses
		List<Map<String, Object>> allCourses = new ArrayList<>();
		for (Subject subject : subjectsAndClasses.keySet())
		{
			List<Clazz> courses = subjectsAndClasses.get(subject).getClasses();
			for (Clazz clazz : courses)
			{
				allCourses.add(clazz.getAttributeValues());
			}
		}
		semesterDataMap.put("classes", allCourses);
		
		// append the map
		objToPHPString(semesterDataMap, 1, builder);
		
		// close the php code
		builder.append(";\n");
		
		// write out the builder's value
		writeHandle.write(builder.toString());
		
		close();
	}
	
	/**
	 * Convert the given listToSerialize into PHP code.
	 *
	 * @param listToSerialize The list to turn into PHP code.
	 * @param depth The number of tabs to use.
	 * @param builder The StringBuilder to append to.
	 * @see #objToPHPString(Object, int, StringBuilder)
	 */
	public static String listToPHPString(List<Object> listToSerialize, int depth)
	{
		StringBuilder retval = new StringBuilder();

		// Don't need to add extra tabs because the calling method of this one
		// should have already done that.
		// Add the code for a new list.
		retval.append("array(\n");
		
		boolean hadPreviousLine = false;
		for (Object val : listToSerialize)
		{
			// write the value
			try
			{
				// add a spacer between the previous line and this line
				if (hadPreviousLine)
				{
					retval.append(",\n");
				}
				hadPreviousLine = true;
				
				// get the whitespace to prepend
				appendSpacing(depth, retval);
				
				// append the value
				objToPHPString(val, depth + 1, retval);
			}
			catch (InvalidParameterException e)
			{
				System.err.println(e);
			}
		}
		
		// add the end of the list
		retval.append("\n");
		appendSpacing(Math.max(0, depth - 1), retval);
		retval.append(")");
		
		return retval.toString();
	}

	/**
	 * Convert the given mapToSerialize into PHP code.
	 *
	 * @param mapToSerialize The map to turn into PHP code.
	 * @param depth The number of tabs to use.
	 * @param builder The StringBuilder to append to.
	 * @see #objToPHPString(Object, int, StringBuilder)
	 */
	public static void mapToPHPString(Map<String, Object> mapToSerialize, int depth, StringBuilder builder)
	{
		// Don't need to add extra tabs because the calling method of this one
		// should have already done that.
		// Add the code for a new list.
		builder.append("array(\n");
		
		boolean hadPreviousLine = false;
		for (String key : mapToSerialize.keySet())
		{
			Object val = mapToSerialize.get(key);
			
			// write the value
			try
			{
				// add a spacer between the previous line and this line
				if (hadPreviousLine)
				{
					builder.append(",\n");
				}
				hadPreviousLine = true;
				
				// get the spacing to prepend
				appendSpacing(depth, builder);
				
				// append the key
				builder.append(String.format("\"%s\" => ", key));
				
				// append the value
				objToPHPString(val, depth + 1, builder);
			}
			catch (InvalidParameterException e)
			{
				System.err.println(e);
			}
		}
		
		// add the end of the list
		builder.append("\n");
		appendSpacing(Math.max(0, depth - 1), builder);
		builder.append(")");
	}

	/**
	 * Append depth number of tabs to the given builder.
	 * 
	 * @param depth The number of tabs to use.
	 * @param builder The StringBuilder to append to.
	 */
	public static void appendSpacing(int depth, StringBuilder builder) {
		for (int i = 0; i < depth; i++)
		{
			builder.append("\t");
		}
	}

	/**
	 * For converting any nested value of maps/lists/primitives into a string
	 * representing PHP code, appended to the given builder.
	 * 
	 * @param val
	 *            The object value to convert.
	 * @param depth
	 *            The number of tabs to insert before each contained object if
	 *            val is either a list or map.
	 * @param depth The number of tabs to use for nested values of val.
	 * @param builder The StringBuilder to append to.
	 * @throws InvalidParameterException If the given val is not a List, Map, or Primitive.
	 */
	@SuppressWarnings({ "unchecked", "rawtypes" })
	public static void objToPHPString(Object val, int depth, StringBuilder builder) throws InvalidParameterException {

		// write the value
		if (val instanceof List)
		{
			// list
			builder.append(listToPHPString((List) val, depth));
		}
		else if (val instanceof Map)
		{
			// map
			mapToPHPString((Map) val, depth, builder);
		}
		else if (PrimitiveHelper.primitiveClasses.contains(val.getClass()))
		{
			// primitive
			builder.append("\"");
			builder.append(val.toString());
			builder.append("\"");
		}
		else
		{
			// unknown
			throw new InvalidParameterException("Unable to serialize " + val + " (class " + val.getClass() + ")");
		}
	}
}
